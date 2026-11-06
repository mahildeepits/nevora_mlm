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
    protected array $rpcEndpoints;

    public function __construct()
    {
        $this->contractAddress = strtolower(env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'));
        $this->apiKey = env('BSCSCAN_API_KEY');
        $this->apiUrl = env('BSCSCAN_API_URL', 'https://api.bscscan.com/api');
        $this->rpcUrl = env('BSC_RPC_URL', 'https://bsc-dataseed.binance.org');
        $this->rpcEndpoints = array_values(array_unique([
            $this->rpcUrl,
            'https://bsc-dataseed.binance.org',
            'https://bsc-dataseed1.defibit.io',
            'https://bsc-dataseed1.ninicoin.io',
            'https://rpc.ankr.com/bsc',
            'https://rpc.bscscan.com',
            'https://binance.llamarpc.com',
            'https://bsc.blockpi.network/v1/rpc/public',
        ]));
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
                            $rawVal = (string) ($tx['value'] ?? '0');

                            if ($providedTxHash && strtolower($txHash) !== strtolower($providedTxHash)) {
                                continue;
                            }

                            if (
                                $from === $payer &&
                                $to === $payee &&
                                $contract === $this->contractAddress &&
                                $this->matchesExpectedAmount($rawVal, $decimal, $expectedAmount) &&
                                (int) ($tx['confirmations'] ?? 0) >= 12
                            ) {
                                if (!UnifiedTransaction::where('tx_hash', $txHash)->exists()) {
                                    return [
                                        'status' => true,
                                        'tx_hash' => $txHash,
                                        'from' => $from,
                                        'to' => $to,
                                        'amount' => $expectedAmount,
                                        'block_number' => $tx['blockNumber'] ?? null,
                                        'confirmations' => (int) ($tx['confirmations'] ?? 0),
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
     * Query BSC JSON-RPC with multi-endpoint failover and retry loop to handle block propagation latency
     */
    protected function verifyViaRpc(string $txHash, string $payer, string $payee, float $expectedAmount): array
    {
        $transferEventSig = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            foreach ($this->rpcEndpoints as $rpc) {
                try {
                    $response = Http::timeout(6)->post($rpc, [
                        'jsonrpc' => '2.0',
                        'method' => 'eth_getTransactionReceipt',
                        'params' => [$txHash],
                        'id' => 1,
                    ]);

                    if (!$response->successful()) {
                        continue;
                    }

                    $json = $response->json();
                    $receipt = $json['result'] ?? null;

                    if (!$receipt) {
                        continue;
                    }

                    // Check execution status: '0x1' is success
                    if (($receipt['status'] ?? '') !== '0x1') {
                        return ['status' => false, 'message' => 'Transaction failed or was reverted on BSC.'];
                    }

                    $logs = $receipt['logs'] ?? [];
                    foreach ($logs as $log) {
                        $contract = strtolower($log['address'] ?? '');
                        $topics = $log['topics'] ?? [];

                        if (strtolower($topics[0] ?? '') === $transferEventSig && count($topics) >= 3) {
                            $logFrom = strtolower('0x' . substr($topics[1], 26));
                            $logTo = strtolower('0x' . substr($topics[2], 26));

                            $hexVal = ltrim($log['data'] ?? '0x0', '0x');
                            $rawDec = $this->bchexdec($hexVal ?: '0');
                            $blockNumber = isset($receipt['blockNumber']) ? hexdec($receipt['blockNumber']) : 0;

                            // Require 12 BSC confirmations before accepting a payment.
                            $latestBlockHex = $this->rpcRequest($rpc, 'eth_blockNumber', []);
                            $latestBlock = $latestBlockHex ? hexdec($latestBlockHex) : 0;
                            $confirmations = $blockNumber > 0 && $latestBlock >= $blockNumber
                                ? ($latestBlock - $blockNumber + 1)
                                : 0;

                            if (
                                $contract === $this->contractAddress &&
                                $logFrom === $payer &&
                                $logTo === $payee &&
                                $this->matchesExpectedAmount($rawDec, 18, $expectedAmount) &&
                                $confirmations >= 12
                            ) {
                                return [
                                    'status' => true,
                                    'tx_hash' => $txHash,
                                    'from' => $logFrom,
                                    'to' => $logTo,
                                    'amount' => $expectedAmount,
                                    'block_number' => $blockNumber,
                                    'confirmations' => $confirmations,
                                ];
                            }
                        }
                    }
                } catch (\Throwable $th) {
                    Log::warning("BSC RPC attempt notice for {$rpc}: " . $th->getMessage());
                }
            }

            // Sleep 1.5 seconds between retry attempts to allow block propagation
            if ($attempt < $maxAttempts) {
                usleep(1500000);
            }
        }

        return ['status' => false, 'message' => 'Transaction verification via RPC did not match expected amount or payee.'];
    }

    /**
     * Compare the token's raw integer units against the expected fee without floats.
     */
    protected function matchesExpectedAmount(string $rawValue, int $decimals, float $expectedAmount): bool
    {
        if ($decimals < 0 || $decimals > 36 || !ctype_digit($rawValue)) {
            return false;
        }

        $scale = bcpow('10', (string) $decimals, 0);
        $expectedUnits = bcmul(number_format($expectedAmount, 2, '.', ''), $scale, 0);

        return bccomp($rawValue, $expectedUnits, 0) === 0;
    }

    /**
     * Perform a JSON-RPC request against a specific endpoint.
     */
    protected function rpcRequest(string $rpc, string $method, array $params): ?string
    {
        try {
            $response = Http::timeout(6)->post($rpc, [
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $params,
                'id' => 1,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $result = $response->json('result');

            return is_string($result) ? $result : null;
        } catch (\\Throwable $th) {
            Log::warning("BSC RPC request failed for {$rpc}: " . $th->getMessage());
            return null;
        }
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
