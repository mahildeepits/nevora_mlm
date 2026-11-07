@extends('layouts.admin')

@section('content')
<style>
    .card-kpi-clickable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-kpi-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-pay-web3 {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: none;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
    }
    .btn-pay-web3:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        transform: translateY(-1px);
    }
    .badge-slot-route {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
        font-weight: 700;
        letter-spacing: 0.5px;
    }
</style>

<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    {{-- Top Header --}}
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fa-solid fa-arrow-up-right-dots text-warning me-2"></i> Reserved Slot Upgrades & Web3 Payouts
                </h4>
                <p class="text-muted small mb-0">
                    Members who have accumulated all 3 reserved payments from downlines (#3, #4, #5) in company escrow. Admin transfers the payout directly to the member's BEP-20 wallet to activate their next slot tier.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btn-admin-connect-wallet" class="btn btn-outline-primary rounded-pill px-3 py-2 font-12 fw-bold d-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-wallet"></i>
                    <span id="admin-wallet-label">Connect Admin Web3 Wallet</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Admin Web3 Connection Banner --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
            <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-shield-halved font-20"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-primary mb-0">Company Reserve & Payout Source</h6>
                            <span class="badge bg-success font-11 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> BSC Mainnet (Chain 56)</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <span>Reserve Wallet: <code class="text-dark fw-bold font-monospace">{{ $companyReserveWallet }}</code></span>
                            <span class="mx-2">•</span>
                            <span>USDT Contract: <code class="text-dark fw-bold font-monospace">{{ maskAddress($usdtContract) }}</code></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" id="admin-wallet-status-box">
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill font-12" id="admin-usdt-badge">
                        <i class="fa-solid fa-circle-notch fa-spin me-1"></i> Checking Wallet...
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Platform KPIs --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi-clickable">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-user-clock font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Upgrades</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingCount) }}</h3>
                    <small class="text-muted font-11">3 Reserved Payments Completed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi-clickable">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-money-bill-transfer font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Payout Amount</h6>
                    <h3 class="fw-bold mb-0 text-danger">{{ formatPrice($pendingAmount) }}</h3>
                    <small class="text-muted font-11">Awaiting 1-Click Transfer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi-clickable">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-circle-check font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Completed Upgrades</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($completedCount) }}</h3>
                    <small class="text-muted font-11">Successfully Transferred</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi-clickable">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-vault font-24"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Upgraded Volume</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ formatPrice($totalPaidAmount) }}</h3>
                    <small class="text-muted font-11">Disbursed from Company Escrow</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            {{-- Tabs & Filters Header --}}
            <div class="card-header bg-transparent border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    {{-- Status Navigation Tabs --}}
                    <ul class="nav nav-pills card-header-pills">
                        <li class="nav-item">
                            <a class="nav-link rounded-pill font-13 fw-semibold {{ $status === 'pending' ? 'active' : '' }}" 
                               href="{{ route('admin.upgrades.index', array_merge(request()->query(), ['status' => 'pending'])) }}">
                               <i class="fa-solid fa-clock-rotate-left me-1"></i> Pending Payouts
                               @if($pendingCount > 0)
                                   <span class="badge bg-danger rounded-pill ms-1.5">{{ $pendingCount }}</span>
                               @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill font-13 fw-semibold {{ $status === 'completed' ? 'active' : '' }}" 
                               href="{{ route('admin.upgrades.index', array_merge(request()->query(), ['status' => 'completed'])) }}">
                               <i class="fa-solid fa-circle-check me-1"></i> Completed Upgrades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded-pill font-13 fw-semibold {{ $status === 'all' ? 'active' : '' }}" 
                               href="{{ route('admin.upgrades.index', array_merge(request()->query(), ['status' => 'all'])) }}">
                               <i class="fa-solid fa-list me-1"></i> All Records
                            </a>
                        </li>
                    </ul>

                    {{-- Search & Slot Filter Form --}}
                    <form method="GET" action="{{ route('admin.upgrades.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                        <input type="hidden" name="status" value="{{ $status }}">

                        <select name="slot" class="form-select form-select-sm rounded-pill font-12" style="width: 170px;" onchange="this.form.submit()">
                            <option value="">All Slot Tiers</option>
                            @foreach($slots as $s)
                                <option value="{{ $s->slot_number }}" {{ request('slot') == $s->slot_number ? 'selected' : '' }}>
                                    Slot {{ $s->slot_number }} (${{ number_format($s->entry_fee, 2) }})
                                </option>
                            @endforeach
                        </select>

                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control rounded-start-pill font-12" placeholder="Member ID, Name, Wallet..." value="{{ request('search') }}">
                            <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>

                        @if(request()->hasAny(['search', 'slot']))
                            <a href="{{ route('admin.upgrades.index', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary rounded-pill font-12" title="Reset Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="font-12 text-uppercase text-muted fw-bold">
                                <th class="ps-4" style="width: 70px;">#</th>
                                <th>Eligible Member</th>
                                <th>BEP-20 Wallet Address</th>
                                <th>Upgrade Path</th>
                                <th>Reserved Escrow</th>
                                <th>Payout Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions / Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upgrades as $upgrade)
                                @php
                                    $user = $upgrade->user;
                                    $hasWallet = !empty($user->wallet_address);
                                    $fromSlotName = $upgrade->fromSlot ? $upgrade->fromSlot->name : "Slot {$upgrade->from_slot_number}";
                                    $toSlotName = $upgrade->toSlot ? $upgrade->toSlot->name : "Slot {$upgrade->to_slot_number}";
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold font-12 text-muted">
                                        #{{ $upgrade->id }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="fa-solid fa-user font-14"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $user->name }}
                                                </a>
                                                <div class="font-11 text-muted font-monospace">
                                                    <span class="badge bg-primary-subtle text-primary">{{ $user->member_id }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($hasWallet)
                                            <div class="d-flex align-items-center gap-1.5">
                                                <code class="font-monospace font-12 text-dark bg-light px-2 py-1 rounded border">
                                                    {{ maskAddress($user->wallet_address) }}
                                                </code>
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0" title="Copy Address" onclick="copyRawText('{{ $user->wallet_address }}', 'Member Wallet Address')">
                                                    <i class="fa-solid fa-copy font-12"></i>
                                                </button>
                                                <a href="https://bscscan.com/address/{{ $user->wallet_address }}" target="_blank" class="btn btn-sm btn-link text-muted p-0" title="View on BscScan">
                                                    <i class="fa-solid fa-arrow-up-right-from-square font-11"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger font-11 px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> No Wallet Linked
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-slot-route px-2.5 py-1 rounded-pill font-11">
                                                Slot {{ $upgrade->from_slot_number }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-muted font-11"></i>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill font-11 fw-bold">
                                                Slot {{ $upgrade->to_slot_number }}
                                            </span>
                                        </div>
                                        <small class="text-muted font-11 d-block mt-0.5">{{ $toSlotName }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1 font-11 fw-bold">
                                            <i class="fa-solid fa-lock me-1"></i> 3 / 3 Payments
                                        </span>
                                        <small class="text-muted font-11 d-block mt-0.5">Payments #3, #4, #5</small>
                                    </td>
                                    <td>
                                        <h6 class="fw-bold text-success mb-0 font-monospace">
                                            ${{ number_format($upgrade->reserved_amount, 2) }} <span class="font-11 text-muted">USDT</span>
                                        </h6>
                                    </td>
                                    <td>
                                        @if($upgrade->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 px-3 py-1.5 rounded-pill font-11 fw-bold d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-clock"></i> Pending Payout
                                            </span>
                                        @elseif($upgrade->status === 'completed')
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill font-11 fw-bold d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i> Paid & Upgraded
                                            </span>
                                            @if($upgrade->paid_at)
                                                <small class="text-muted font-11 d-block mt-0.5">{{ $upgrade->paid_at->format('d M Y, h:i A') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary rounded-pill font-11">{{ ucfirst($upgrade->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($upgrade->status === 'pending')
                                            @if($hasWallet)
                                                <div class="d-inline-flex align-items-center gap-1.5">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-pay-web3 rounded-pill px-3 py-1.5 font-12 fw-bold d-inline-flex align-items-center gap-1.5"
                                                            onclick="initiateAdminUpgradePayout({{ $upgrade->id }}, '{{ $user->member_id }}', '{{ $user->wallet_address }}', {{ $upgrade->reserved_amount }}, {{ $upgrade->to_slot_number }})">
                                                        <i class="fa-solid fa-bolt"></i> Click & Pay ${{ number_format($upgrade->reserved_amount, 2) }}
                                                    </button>

                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5 font-11" 
                                                            title="Enter External Transaction Hash"
                                                            onclick="openManualTxModal({{ $upgrade->id }}, '{{ $user->member_id }}', '{{ $user->wallet_address }}', {{ $upgrade->reserved_amount }}, {{ $upgrade->to_slot_number }})">
                                                        <i class="fa-solid fa-receipt"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3 py-1.5 font-12 disabled" disabled title="User must link a wallet address first">
                                                    No Wallet Linked
                                                </button>
                                            @endif
                                        @elseif($upgrade->status === 'completed')
                                            <div class="d-inline-flex flex-column align-items-end">
                                                @if($upgrade->tx_hash)
                                                    <a href="https://bscscan.com/tx/{{ $upgrade->tx_hash }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 font-11 d-inline-flex align-items-center gap-1">
                                                        <i class="fa-solid fa-receipt"></i> 
                                                        <span>{{ substr($upgrade->tx_hash, 0, 6) }}...{{ substr($upgrade->tx_hash, -4) }}</span>
                                                        <i class="fa-solid fa-arrow-up-right-from-square font-10"></i>
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-muted font-11">Manual Upgrade</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-arrow-up-right-dots fa-3x mb-3 text-secondary opacity-50"></i>
                                            <h6 class="fw-bold mb-1">No Upgrade Requests Found</h6>
                                            <p class="font-12 mb-0">There are no members currently matching this filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($upgrades->hasPages())
                    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted font-12">
                            Showing {{ $upgrades->firstItem() }} to {{ $upgrades->lastItem() }} of {{ $upgrades->total() }} records
                        </small>
                        {{ $upgrades->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Manual Transaction Hash Modal --}}
<div class="modal fade" id="manualTxModal" tabindex="-1" aria-labelledby="manualTxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold" id="manualTxModalLabel">
                    <i class="fa-solid fa-receipt text-primary me-1.5"></i> Record Manual Payout & Upgrade
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted font-12 mb-3">
                    If you transferred the upgrade payout from an external hardware wallet or exchange, enter the confirmed BSC transaction hash below.
                </p>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between font-12 mb-1">
                        <span class="text-muted">Beneficiary Member:</span>
                        <span class="fw-bold text-dark" id="modal-member-id">-</span>
                    </div>
                    <div class="d-flex justify-content-between font-12 mb-1">
                        <span class="text-muted">Beneficiary Wallet:</span>
                        <span class="fw-bold font-monospace text-dark" id="modal-member-wallet">-</span>
                    </div>
                    <div class="d-flex justify-content-between font-12">
                        <span class="text-muted">Upgrade Slot:</span>
                        <span class="fw-bold text-success" id="modal-target-slot">-</span>
                    </div>
                </div>

                <form id="manual-tx-form" onsubmit="submitManualUpgrade(event)">
                    <input type="hidden" id="manual-upgrade-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-12">BSC Transaction Hash (TxHash) <span class="text-danger">*</span></label>
                        <input type="text" id="manual-tx-hash-input" class="form-control font-monospace font-12" placeholder="0x..." required>
                        <small class="text-muted font-11">Must be confirmed on BNB Smart Chain.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold font-12">Sender Wallet Address (Admin/Company)</label>
                        <input type="text" id="manual-sender-wallet-input" class="form-control font-monospace font-12" value="{{ $companyReserveWallet }}">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 font-12" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit-manual-tx" class="btn btn-primary rounded-pill px-4 font-12 fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Confirm & Upgrade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const BSC_CHAIN_ID = 56;
    const USDT_CONTRACT_ADDRESS = '{{ $usdtContract }}';
    const BEP20_ABI = [
        "function balanceOf(address owner) view returns (uint256)",
        "function transfer(address to, uint256 amount) returns (bool)",
        "function decimals() view returns (uint8)"
    ];

    let currentAdminAddress = null;
    let manualModalInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        manualModalInstance = new bootstrap.Modal(document.getElementById('manualTxModal'));
        checkAdminWeb3Wallet();

        const btnConnect = document.getElementById('btn-admin-connect-wallet');
        if (btnConnect) {
            btnConnect.addEventListener('click', async function () {
                if (typeof NevoraWeb3 !== 'undefined' && NevoraWeb3.showWalletModal) {
                    NevoraWeb3.showWalletModal({
                        title: 'Connect Admin Web3 Wallet',
                        subtitle: 'Select your wallet to disburse USDT upgrade payouts',
                        onSelect: async (walletId) => {
                            await checkAdminWeb3Wallet();
                        }
                    });
                } else if (window.ethereum) {
                    try {
                        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                        if (accounts && accounts.length > 0) {
                            await checkAdminWeb3Wallet();
                        }
                    } catch (e) {
                        toastr.error('Wallet connection rejected: ' + e.message);
                    }
                }
            });
        }
    });

    async function checkAdminWeb3Wallet() {
        const label = document.getElementById('admin-wallet-label');
        const badge = document.getElementById('admin-usdt-badge');

        try {
            if (window.ethereum) {
                const accounts = await window.ethereum.request({ method: 'eth_accounts' });
                if (accounts && accounts.length > 0) {
                    currentAdminAddress = accounts[0].toLowerCase();
                    if (label) {
                        label.innerHTML = `<span class="font-monospace text-primary fw-bold">${currentAdminAddress.slice(0, 6)}...${currentAdminAddress.slice(-4)}</span>`;
                    }

                    // Query USDT Balance on BSC
                    if (typeof NevoraWeb3 !== 'undefined' && NevoraWeb3.getUsdtBalance) {
                        const bal = await NevoraWeb3.getUsdtBalance(currentAdminAddress);
                        if (badge) {
                            badge.className = 'badge bg-success-subtle text-success px-3 py-2 rounded-pill font-12 fw-bold';
                            badge.innerHTML = `<i class="fa-solid fa-wallet me-1"></i> Admin Balance: $${bal.toFixed(2)} USDT`;
                        }
                    }
                    return;
                }
            }
        } catch (e) {
            console.warn('Admin wallet balance query error:', e);
        }

        if (badge) {
            badge.className = 'badge bg-warning-subtle text-warning px-3 py-2 rounded-pill font-12 fw-bold';
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Wallet Disconnected';
        }
    }

    /**
     * Web3 1-Click Pay & Upgrade Workflow
     */
    async function initiateAdminUpgradePayout(upgradeId, memberId, recipientAddress, amountUsdt, targetSlotNumber) {
        if (!window.ethereum && (typeof NevoraWeb3 === 'undefined')) {
            Swal.fire({
                icon: 'warning',
                title: 'Web3 Wallet Required',
                text: 'Please install MetaMask or open this page in a Web3 browser (SafePal / Trust Wallet) to execute 1-click payouts.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // 1. Ensure Wallet is Connected
        let provider = null;
        let signer = null;
        let adminAddress = currentAdminAddress;

        try {
            if (window.ethereum) {
                const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                adminAddress = accounts[0];
                currentAdminAddress = adminAddress;
                provider = new ethers.BrowserProvider(window.ethereum);
                signer = await provider.getSigner();

                // Switch to BSC Mainnet if needed
                const network = await provider.getNetwork();
                if (Number(network.chainId) !== BSC_CHAIN_ID) {
                    try {
                        await window.ethereum.request({
                            method: 'wallet_switchEthereumChain',
                            params: [{ chainId: '0x38' }],
                        });
                    } catch (switchError) {
                        if (switchError.code === 4902) {
                            await window.ethereum.request({
                                method: 'wallet_addEthereumChain',
                                params: [{
                                    chainId: '0x38',
                                    chainName: 'BNB Smart Chain',
                                    nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 },
                                    rpcUrls: ['https://bsc-dataseed.binance.org'],
                                    blockExplorerUrls: ['https://bscscan.com'],
                                }],
                            });
                        } else {
                            throw switchError;
                        }
                    }
                }
            } else {
                throw new Error('No active Web3 provider found.');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Wallet Connection Error',
                text: err.message || 'Could not connect to Web3 wallet.',
            });
            return;
        }

        // 2. Check Admin USDT Balance
        let usdtBalance = 0;
        try {
            if (typeof NevoraWeb3 !== 'undefined' && NevoraWeb3.getUsdtBalance) {
                usdtBalance = await NevoraWeb3.getUsdtBalance(adminAddress);
            }
        } catch (e) {
            console.warn(e);
        }

        if (usdtBalance > 0 && usdtBalance < amountUsdt) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient USDT Balance',
                html: `Your connected admin wallet has <b>$${usdtBalance.toFixed(2)} USDT</b>, but <b>$${amountUsdt.toFixed(2)} USDT</b> is required for this payout. Please deposit USDT into your admin wallet.`,
            });
            return;
        }

        // 3. Confirm with Admin
        const confirmResult = await Swal.fire({
            title: `Transfer $${parseFloat(amountUsdt).toFixed(2)} USDT?`,
            html: `
                <div class="p-3 bg-light rounded-4 text-start mb-3 border font-13">
                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                        <span class="text-muted">Eligible Member:</span>
                        <span class="fw-bold font-monospace text-primary">${escapeHtml(memberId)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                        <span class="text-muted">Recipient Wallet:</span>
                        <span class="fw-bold font-monospace text-dark">${escapeHtml(recipientAddress.slice(0, 8))}...${escapeHtml(recipientAddress.slice(-6))}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                        <span class="text-muted">Target Slot:</span>
                        <span class="fw-bold text-success">Slot ${targetSlotNumber}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Amount to Pay:</span>
                        <span class="fw-bold text-success font-monospace">$${parseFloat(amountUsdt).toFixed(2)} USDT (BEP-20)</span>
                    </div>
                </div>
                <p class="font-12 text-muted mb-0">MetaMask will prompt you to authorize the transfer. Upon confirmation on BSC, the user will be transferred into Slot ${targetSlotNumber} immediately.</p>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-bolt me-1"></i> Authorize & Pay',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
        });

        if (!confirmResult.isConfirmed) return;

        // 4. Prompt Transaction in Wallet
        Swal.fire({
            title: 'Authorizing Payout in Wallet...',
            html: '<div class="spinner-border text-primary my-3" role="status"></div><p class="font-12 text-muted">Please confirm the transfer of $' + amountUsdt + ' USDT in your wallet app...</p>',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        try {
            const usdtContract = new ethers.Contract(USDT_CONTRACT_ADDRESS, BEP20_ABI, signer);
            const amountWei = ethers.parseUnits(amountUsdt.toString(), 18);

            const tx = await usdtContract.transfer(recipientAddress, amountWei, {
                gasLimit: 100000n
            });

            // 5. Transaction Broadcasted -> Wait for Confirmation
            Swal.fire({
                title: 'Validating on BSC...',
                html: `
                    <div class="my-3 text-center">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <p class="font-13 text-muted mb-2">Payout broadcasted successfully!</p>
                    <p class="font-monospace font-11 text-muted text-break p-2 bg-light rounded border">${tx.hash}</p>
                    <small class="text-muted font-11"><i class="fa-solid fa-clock me-1"></i> Waiting for BNB Smart Chain confirmation...</small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            // Wait for receipt
            let receipt = null;
            if (typeof pollTransactionReceipt === 'function') {
                receipt = await pollTransactionReceipt(tx.hash, 30);
            } else {
                receipt = await tx.wait(1);
            }

            // 6. Submit to Backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/admin/upgrades/${upgradeId}/pay-and-upgrade`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tx_hash: tx.hash,
                    admin_wallet: adminAddress
                })
            });

            const data = await res.json();
            if (data && data.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Upgrade Payout Completed!',
                    text: data.message || 'Member successfully paid and transferred to next slot.',
                    confirmButtonText: 'Done',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data?.message || 'Server failed to finalize the upgrade record.');
            }
        } catch (err) {
            console.error('Upgrade Payout Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Payout Failed',
                text: err.reason || err.message || 'Transaction could not be completed.',
                confirmButtonText: 'Close',
                confirmButtonColor: '#ef4444'
            });
        }
    }

    /**
     * Open Manual Transaction Hash Entry Modal
     */
    function openManualTxModal(upgradeId, memberId, recipientAddress, amountUsdt, targetSlotNumber) {
        document.getElementById('manual-upgrade-id').value = upgradeId;
        document.getElementById('modal-member-id').innerText = memberId;
        document.getElementById('modal-member-wallet').innerText = recipientAddress.slice(0, 10) + '...' + recipientAddress.slice(-6);
        document.getElementById('modal-target-slot').innerText = `Slot ${targetSlotNumber} ($${parseFloat(amountUsdt).toFixed(2)} USDT)`;
        document.getElementById('manual-tx-hash-input').value = '';

        if (manualModalInstance) {
            manualModalInstance.show();
        }
    }

    /**
     * Submit Manual Transaction Hash
     */
    async function submitManualUpgrade(e) {
        e.preventDefault();
        const upgradeId = document.getElementById('manual-upgrade-id').value;
        const txHash = document.getElementById('manual-tx-hash-input').value.trim();
        const senderWallet = document.getElementById('manual-sender-wallet-input').value.trim();
        const btnSubmit = document.getElementById('btn-submit-manual-tx');

        if (!txHash) {
            toastr.error('Please enter a valid BSC transaction hash.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying on BSC...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/admin/upgrades/${upgradeId}/pay-and-upgrade`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tx_hash: txHash,
                    admin_wallet: senderWallet
                })
            });

            const data = await res.json();
            if (data && data.status) {
                if (manualModalInstance) manualModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Upgrade Verified & Completed!',
                    text: data.message,
                    confirmButtonText: 'Done',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data?.message || 'Verification failed.');
            }
        } catch (err) {
            toastr.error(err.message || 'Failed to submit manual upgrade.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-check me-1"></i> Confirm & Upgrade';
        }
    }

    function copyRawText(text, label) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                toastr.success(label + ' copied to clipboard!');
            });
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text || '';
        return div.innerHTML;
    }
</script>
@endpush
@endsection

