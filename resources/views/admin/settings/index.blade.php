@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <x-alerts />
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom p-4 pb-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-gears text-primary me-2"></i> System Settings & Maintenance
                        </h4>
                        <p class="text-muted small mb-0">Manage official company contact details, public support links, and software database maintenance.</p>
                    </div>
                </div>

                {{-- Navigation Tabs --}}
                <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') != 'email' && request('tab') != 'reset' ? 'active' : '' }} fw-bold py-3 px-4" id="company-tab" data-bs-toggle="tab" href="#company-pane" role="tab">
                            <i class="fa-solid fa-building me-2 text-primary"></i> Company & Support Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'email' ? 'active' : '' }} fw-bold py-3 px-4" id="email-tab" data-bs-toggle="tab" href="#email-pane" role="tab">
                            <i class="fa-solid fa-envelope-circle-check me-2 text-primary"></i> Email & Hostinger SMTP
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'reset' ? 'active' : '' }} fw-bold py-3 px-4 text-danger" id="reset-tab" data-bs-toggle="tab" href="#reset-pane" role="tab">
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Software Data Reset
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="settingsTabsContent">
                    
                    {{-- TAB 1: Company Profile & Support Details --}}
                    <div class="tab-pane fade {{ request('tab') != 'email' && request('tab') != 'reset' ? 'show active' : '' }}" id="company-pane" role="tabpanel">
                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf
                            
                            <div class="row g-4">
                                {{-- Section: Brand & Contact Info --}}
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary text-uppercase font-12 mb-3">
                                        <i class="fa-solid fa-id-card me-1"></i> Public Company & Contact Info (Shown to Members)
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Platform / Software Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-gem text-primary"></i></span>
                                        <input type="text" name="software_name" class="form-control" value="{{ old('software_name', $settings['software_name'] ?? 'NEVORA') }}" required>
                                    </div>
                                    <small class="text-muted font-11">Branded name displayed across navbar, dashboard, and browser title.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Official Support Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                                        <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $settings['company_email'] ?? ($settings['support_email'] ?? 'support@nevora.com')) }}" placeholder="support@nevora.com">
                                    </div>
                                    <small class="text-muted font-11">Member dashboard support card email link.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Official Phone / Helpline</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-primary"></i></span>
                                        <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone'] ?? '+1 (555) 019-2834') }}" placeholder="e.g. +1 234 567 8900">
                                    </div>
                                    <small class="text-muted font-11">Helpline number displayed to members for platform assistance.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">WhatsApp Support Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                                        <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" placeholder="e.g. +1234567890">
                                    </div>
                                    <small class="text-muted font-11">Enables 1-click WhatsApp chat support button on member dashboard.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Official Telegram Channel / Group Link</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-brands fa-telegram text-info"></i></span>
                                        <input type="text" name="telegram_link" class="form-control" value="{{ old('telegram_link', $settings['telegram_link'] ?? 'https://t.me/nevora_official') }}" placeholder="https://t.me/your_channel">
                                    </div>
                                    <small class="text-muted font-11">Community channel link for announcements and member updates.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-13">Company Office / Headquarters Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                        <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $settings['company_address'] ?? 'Nevora Digital Assets Tower, Suite 400') }}" placeholder="e.g. Suite 400, Financial District">
                                    </div>
                                    <small class="text-muted font-11">Displayed in platform footer and support information.</small>
                                </div>

                                <div class="col-12 mt-4">
                                    <hr class="opacity-25">
                                    <h6 class="fw-bold text-primary text-uppercase font-12 mb-3">
                                        <i class="fa-solid fa-wallet me-1"></i> Blockchain & Core Platform Wallets
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">BSC USDT Contract Address</label>
                                    <input type="text" name="usdt_contract" class="form-control font-monospace font-12" value="{{ old('usdt_contract', $settings['usdt_contract'] ?? '0x55d398326f99059fF775485246999027B3197955') }}">
                                    <small class="text-muted font-11">Official Binance-Peg BSC USDT BEP-20 Contract.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">Company Reserve Wallet</label>
                                    <input type="text" name="company_reserve_wallet" class="form-control font-monospace font-12" value="{{ old('company_reserve_wallet', $settings['company_reserve_wallet'] ?? '0x1111111111111111111111111111111111111111') }}">
                                    <small class="text-muted font-11">Decentralized company escrow & reserve recipient.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-13">Company Revenue Wallet</label>
                                    <input type="text" name="company_revenue_wallet" class="form-control font-monospace font-12" value="{{ old('company_revenue_wallet', $settings['company_revenue_wallet'] ?? '0x2222222222222222222222222222222222222222') }}">
                                    <small class="text-muted font-11">Deduction revenue recipient (Slot 3-11 fees).</small>
                                </div>

                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-pill fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 2: Email & Hostinger SMTP Settings --}}
                    <div class="tab-pane fade {{ request('tab') == 'email' ? 'show active' : '' }}" id="email-pane" role="tabpanel">
                        <div class="row g-4">
                            
                            {{-- Status Alert / Card --}}
                            <div class="col-12">
                                <div class="p-3.5 rounded-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary text-white p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                            <i class="fa-solid fa-server font-20"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-primary mb-0.5">Hostinger SMTP Mailer Status</h6>
                                            <p class="text-muted small mb-0">
                                                Connected to <strong>{{ $mailConfig['host'] ?? 'smtp.hostinger.com' }}:{{ $mailConfig['port'] ?? 465 }}</strong> via <strong>{{ strtoupper($mailConfig['scheme'] ?? 'SMTPS') }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        @if(!empty($mailConfig['has_password']))
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-12 fw-bold">
                                                <i class="fa-solid fa-shield-check me-1"></i> Mailbox Password Configured
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill font-12 fw-bold">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Password Needed to Send
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Left Column: SMTP Configuration Form --}}
                            <div class="col-lg-7">
                                <div class="card border border-light-subtle rounded-4 h-100 shadow-none">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-dark text-uppercase font-12 mb-3">
                                            <i class="fa-solid fa-sliders me-1.5 text-primary"></i> Hostinger SMTP Connection Details
                                        </h6>
                                        <p class="text-muted small mb-4">
                                            Configure the outgoing SMTP credentials for <strong>{{ $mailConfig['username'] ?? 'info@nevora.world' }}</strong> to deliver customer OTPs and notifications.
                                        </p>

                                        <form action="{{ route('admin.settings.update_smtp') }}" method="POST">
                                            @csrf
                                            
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-bold font-13">SMTP Host <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-network-wired text-muted"></i></span>
                                                        <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $mailConfig['host'] ?? 'smtp.hostinger.com') }}" required>
                                                    </div>
                                                    <small class="text-muted font-11">Hostinger SMTP Server (default: smtp.hostinger.com)</small>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold font-13">Port <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag text-muted"></i></span>
                                                        <input type="number" name="mail_port" class="form-control" value="{{ old('mail_port', $mailConfig['port'] ?? 465) }}" required>
                                                    </div>
                                                    <small class="text-muted font-11">465 (SSL) / 587 (TLS)</small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold font-13">Email Address / Username <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                                        <input type="email" name="mail_username" class="form-control" value="{{ old('mail_username', $mailConfig['username'] ?? 'info@nevora.world') }}" required>
                                                    </div>
                                                    <small class="text-muted font-11">Your full Hostinger webmail address.</small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold font-13">
                                                        Email Password 
                                                        @if(!empty($mailConfig['has_password']))
                                                            <span class="badge bg-success-subtle text-success ms-1 font-10">Configured</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger ms-1 font-10">Required</span>
                                                        @endif
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                                        <input type="password" name="mail_password" class="form-control" placeholder="{{ !empty($mailConfig['has_password']) ? '•••••••••••• (Leave blank to keep current)' : 'Enter your Hostinger email password' }}">
                                                    </div>
                                                    <small class="text-muted font-11">The password you set when creating this email on Hostinger hPanel.</small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold font-13">Sender Address (From) <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-paper-plane text-muted"></i></span>
                                                        <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $mailConfig['from_address'] ?? 'info@nevora.world') }}" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold font-13">Sender Name <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-id-badge text-muted"></i></span>
                                                        <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $mailConfig['from_name'] ?? 'NEVORA') }}" required>
                                                    </div>
                                                </div>

                                                <div class="col-12 mt-3 text-end">
                                                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> Save SMTP Credentials
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column: Live SMTP Test Tool --}}
                            <div class="col-lg-5">
                                <div class="card border border-light-subtle rounded-4 h-100 shadow-none bg-light bg-opacity-40">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-dark text-uppercase font-12 mb-3">
                                            <i class="fa-solid fa-vial me-1.5 text-success"></i> Live Test Email Sender
                                        </h6>
                                        <p class="text-muted small mb-3" style="line-height: 1.6;">
                                            Send a live test verification OTP email to any email address to verify that your Hostinger SMTP credentials and SSL handshake on port 465 are working.
                                        </p>

                                        <form action="{{ route('admin.settings.test_email') }}" method="POST">
                                            @csrf
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold font-13">Recipient Email Address <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="fa-solid fa-at text-muted"></i></span>
                                                    <input type="email" name="test_email" class="form-control" placeholder="e.g. yourname@gmail.com" required>
                                                </div>
                                                <small class="text-muted font-11">Enter your personal email to receive the test OTP message.</small>
                                            </div>

                                            <div class="d-grid mb-3">
                                                <button type="submit" class="btn btn-outline-primary py-2.5 rounded-pill fw-bold shadow-sm">
                                                    <i class="fa-solid fa-paper-plane me-1.5"></i> Send Live Test Email
                                                </button>
                                            </div>

                                            <div class="p-3 rounded-3 bg-white border border-light-subtle">
                                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                                    <i class="fa-solid fa-circle-info text-primary font-14"></i>
                                                    <span class="fw-bold font-12 text-dark">CLI Command Also Available:</span>
                                                </div>
                                                <code class="d-block p-2 rounded bg-dark text-light font-11 font-monospace">
                                                    php artisan mail:test recipient@email.com
                                                </code>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Software Data Reset (Danger Zone) --}}
                    <div class="tab-pane fade {{ request('tab') == 'reset' ? 'show active' : '' }}" id="reset-pane" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-12 col-xl-10">
                                
                                {{-- Danger Zone Card --}}
                                <div class="p-4 rounded-4 border border-danger border-opacity-50 bg-danger bg-opacity-10 mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                            <i class="fa-solid fa-triangle-exclamation font-24"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-danger mb-1">Software Data Reset (Danger Zone)</h5>
                                            <p class="mb-0 text-muted small" style="line-height: 1.6;">
                                                This maintenance tool is used to purge all test data before launching the platform live or restarting system operations.
                                                Please read the preservation rules carefully before proceeding.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Scope Comparison Table --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3.5 rounded-3 border border-danger border-opacity-25 bg-white h-100 shadow-sm">
                                            <h6 class="fw-bold text-danger mb-2 font-13">
                                                <i class="fa-solid fa-trash-can me-1"></i> Data Cleared on Reset:
                                            </h6>
                                            <ul class="mb-0 small text-muted ps-3 font-12" style="line-height: 1.7;">
                                                <li>All regular registered test members & downline networks</li>
                                                <li>All slot positions & placement trees</li>
                                                <li>All autopool recycle cycle logs</li>
                                                <li>All transaction ledger records & payment proofs</li>
                                                <li>All temporary email verification OTP logs</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3.5 rounded-3 border border-success border-opacity-25 bg-white h-100 shadow-sm">
                                            <h6 class="fw-bold text-success mb-2 font-13">
                                                <i class="fa-solid fa-shield-check me-1"></i> Strictly Preserved & Pre-Positioned:
                                            </h6>
                                            <ul class="mb-0 small text-muted ps-3 font-12" style="line-height: 1.7;">
                                                <li><strong>Admin Account (<span class="text-primary font-monospace">ADMIN01</span>)</strong> with login password intact</li>
                                                <li><strong>Company Root (<span class="text-primary font-monospace">COMPANY01</span> - Account A)</strong> at Slot 12</li>
                                                <li><strong>Root Member (<span class="text-primary font-monospace">MEMBER01</span> - Account B)</strong> placed under A</li>
                                                <li><strong>Both A and B pre-positioned across all 12 slots & recycle pools</strong></li>
                                                <li>All 12 Dynamic Slots configuration & entry fees</li>
                                                <li>All custom company details (phone, email, wallets, address)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Current Database Counters --}}
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border mb-4 flex-wrap gap-2">
                                    <span class="small fw-bold text-dark">Current System Records to be Purged:</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning-subtle text-warning px-3 py-1.5 rounded-pill font-11 fw-bold">
                                            {{ $stats['regular_members'] }} Test Members
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger px-3 py-1.5 rounded-pill font-11 fw-bold">
                                            {{ $stats['total_transactions'] }} Transactions
                                        </span>
                                        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill font-11 fw-bold">
                                            {{ $stats['total_positions'] }} Matrix Positions
                                        </span>
                                    </div>
                                </div>

                                {{-- Action Trigger Button --}}
                                <div class="text-center pt-2">
                                    <button type="button" class="btn btn-danger px-5 py-2.5 rounded-pill fw-bold shadow" data-bs-toggle="modal" data-bs-target="#confirmResetModal">
                                        <i class="fa-solid fa-arrows-rotate me-1.5"></i> Reset Software Data Now
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
{{-- Security Confirmation Modal for Database Reset --}}
<div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-shield-halved font-18"></i>
                    <h6 class="modal-title fw-bold text-white mb-0" id="confirmResetModalLabel">
                        Security Verification: Confirm Software Reset
                    </h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.settings.reset_database') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 small mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1 text-warning font-14"></i>
                        This action will wipe all test members and transaction records. <strong>Admin, Company Root (A), and Member (B)</strong> will be preserved and pre-positioned in all 12 slots and recycle pools.
                    </div>

                    {{-- Admin Password Verification --}}
                    <div class="mb-3">
                        <label class="form-label font-13 fw-bold">Enter Your Current Admin Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="admin_password" class="form-control" placeholder="Admin password" required autofocus>
                        </div>
                        <small class="text-muted font-11">Required to verify administrator identity.</small>
                    </div>

                    {{-- Security Keyword Confirmation --}}
                    <div class="mb-2">
                        <label class="form-label font-13 fw-bold">
                            Type <span class="badge bg-danger text-white font-monospace px-2">RESET-DATABASE</span> to confirm: <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="confirm_phrase" class="form-control font-monospace" placeholder="RESET-DATABASE" required autocomplete="off">
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Confirm & Reset Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
