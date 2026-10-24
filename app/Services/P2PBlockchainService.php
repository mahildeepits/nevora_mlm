<?php

namespace App\Services;

use App\Models\UnifiedTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class P2PBlockchainService
{
    protected string $contractAddress;
    protected ?string $apiKey;
    protected string $apiUrl;
    protected string $rpcUrl;

    public function __construct()
    {
        $this->contractAddress = strtolower(env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'));
        $this->apiKey = env('BSCSCAN_API_KEY');
        $this->apiUrl = env('BSCSCAN_API_URL', 'https://api.bscscan.com/api');
        $this->rpcUrl = env('BSC_RPC_URL', 'https://bsc-dataseed.binance.org');
    }

    /**
     * Find and verify a matching on-chain BEP-20 USDT transfer from payer to payee
     */
    public function verifyTransfer(string $payerAddress, string $payeeAddress, float $expectedAmount, ?string $providedTxHash = null): array
    {
        $payer = strtolower(trim($payerAddress));
        $payee = strtolower(trim($payeeAddress));

        // 1. If explicit tx hash provided, check if already claimed
        if ($providedTxHash) {
            $providedTxHash = trim($providedTxHash);

            $existing = UnifiedTransaction::where('tx_hash', $providedTxHash)->first();
            if ($existing) {
                return [
                    'status' => false,
                    'message' => 'This transaction hash has already been registered in the system.',
                ];
            }

            // Local development simulation mode: allow validly formatted test hashes
            if (app()->environment('local') && str_starts_with($providedTxHash, '0xtest_')) {
                return [
                    'status' => true,
                    'tx_hash' => $providedTxHash,
                    'from' => $payer,
                    'to' => $payee,
                    'amount' => $expectedAmount,
                    'is_simulated' => true,
                ];
            }

            // Verify directly on BSC via Public JSON-RPC (Instant, Free, No API key needed)
            $rpcResult = $this->verifyViaRpc($providedTxHash, $payer, $payee, $expectedAmount);
            if ($rpcResult['status']) {
                return $rpcResult;
            }
        }

        // 2. Query BscScan for token transfers (if API key is configured or tx_hash wasn't provided)
        if ($this->apiKey) {
            try {
                $response = Http::timeout(10)->get($this->apiUrl, [
                    'module' => 'account',
                    'action' => 'tokentx',
                    'contractaddress' => $this->contractAddress,
                    'address' => $payer,
                    'page' => 1,
                    'offset' => 20,
                    'sort' => 'desc',
                    'apikey' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $transfers = $data['result'] ?? [];

                    if (is_array($transfers)) {
                        foreach ($transfers as $tx) {
                            $txHash = $tx['hash'] ?? '';
                            $from = strtolower($tx['from'] ?? '');
                            $to = strtolower($tx['to'] ?? '');
                            $contract = strtolower($tx['contractAddress'] ?? '');
                            $decimal = (int) ($tx['tokenDecimal'] ?? 18);
                            $rawVal = (float) ($tx['value'] ?? 0);
                            $actualAmount = $rawVal / pow(10, $decimal);

                            if ($providedTxHash && strtolower($txHash) !== strtolower($providedTxHash)) {
                                continue;
                            }

                            if (
                                $from === $payer &&
                                $to === $payee &&
                                $contract === $this->contractAddress &&
                                abs($actualAmount - $expectedAmount) < 0.05
                            ) {
                                if (!UnifiedTransaction::where('tx_hash', $txHash)->exists()) {
                                    return [
                                        'status' => true,
                                        'tx_hash' => $txHash,
                                        'from' => $from,
                                        'to' => $to,
                                        'amount' => $actualAmount,
                                        'block_number' => $tx['blockNumber'] ?? null,
                                    ];
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                Log::error("BscScan API Query Exception: " . $th->getMessage());
            }
        }

        return [
            'status' => false,
            'message' => 'No matching on-chain transfer found from your wallet to the assigned address for $' . number_format($expectedAmount, 2) . ' USDT. Please ensure the transaction has confirmed on BSC.',
        ];
    }

    /**
     * Query BSC JSON-RPC directly to inspect transaction receipt and token transfer event
     */
    protected function verifyViaRpc(string $txHash, string $payer, string $payee, float $expectedAmount): array
    {
        try {
            $response = Http::timeout(8)->post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_getTransactionReceipt',
                'params' => [$txHash],
                'id' => 1,
            ]);

            if (!$response->successful()) {
                return ['status' => false, 'message' => 'Could not connect to BSC RPC node.'];
            }

            $json = $response->json();
            $receipt = $json['result'] ?? null;

            if (!$receipt) {
                return ['status' => false, 'message' => 'Transaction not found or still pending in mempool.'];
            }

            // Check execution status: '0x1' is success
            if (($receipt['status'] ?? '') !== '0x1') {
                return ['status' => false, 'message' => 'Transaction failed or was reverted on BSC.'];
            }

            // ERC20/BEP20 Transfer(address,address,uint256) signature hash
            $transferEventSig = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
            $logs = $receipt['logs'] ?? [];

            foreach ($logs as $log) {
                $contract = strtolower($log['address'] ?? '');
                $topics = $log['topics'] ?? [];

                // Check if this log is a Transfer event
                if (strtolower($topics[0] ?? '') === $transferEventSig && count($topics) >= 3) {
                    $logFrom = strtolower('0x' . substr($topics[1], 26));
                    $logTo = strtolower('0x' . substr($topics[2], 26));

                    // Decode hex value (BEP-20 USDT has 18 decimals)
                    $hexVal = ltrim($log['data'] ?? '0x0', '0x');
                    $rawDec = $this->bchexdec($hexVal ?: '0');
                    $actualAmount = (float) bcdiv($rawDec, '1000000000000000000', 4);

                    // Validate: contract, sender, receiver, and amount
                    if (
                        $contract === $this->contractAddress &&
                        $logFrom === $payer &&
                        $logTo === $payee &&
                        abs($actualAmount - $expectedAmount) < 0.05
                    ) {
                        return [
                            'status' => true,
                            'tx_hash' => $txHash,
                            'from' => $logFrom,
                            'to' => $logTo,
                            'amount' => $actualAmount,
                            'block_number' => isset($receipt['blockNumber']) ? hexdec($receipt['blockNumber']) : null,
                        ];
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error("BSC RPC verification error for tx {$txHash}: " . $th->getMessage());
        }

        return ['status' => false, 'message' => 'Transaction verification via RPC did not match expected amount or payee.'];
    }

    /**
     * Accurate arbitrary-precision hex to decimal conversion
     */
    protected function bchexdec(string $hex): string
    {
        $hex = ltrim($hex, '0x');
        $dec = '0';
        $len = strlen($hex);
        for ($i = 0; $i < $len; $i++) {
            $dec = bcadd(bcmul($dec, '16'), hexdec($hex[$i]));
        }
        return $dec;
    }
}
