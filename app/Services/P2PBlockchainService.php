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

    public function __construct()
    {
        $this->contractAddress = strtolower(env('USDT_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'));
        $this->apiKey = env('BSCSCAN_API_KEY');
        $this->apiUrl = env('BSCSCAN_API_URL', 'https://api.bscscan.com/api');
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
        }

        // 2. Query BscScan for token transfers
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

                        // If provided tx hash, match specific hash
                        if ($providedTxHash && strtolower($txHash) !== strtolower($providedTxHash)) {
                            continue;
                        }

                        // Check sender, receiver, contract, and amount tolerance (within 0.01 USDT)
                        if (
                            $from === $payer &&
                            $to === $payee &&
                            $contract === $this->contractAddress &&
                            abs($actualAmount - $expectedAmount) < 0.05
                        ) {
                            // Check if hash already used
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

        return [
            'status' => false,
            'message' => 'No matching on-chain transfer found from your wallet to the assigned address for $' . number_format($expectedAmount, 2) . ' USDT. Please ensure the transaction has confirmed on BSC.',
        ];
    }
}
