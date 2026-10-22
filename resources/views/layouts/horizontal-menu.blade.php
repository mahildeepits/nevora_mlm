<div class="navbar dual-horizontal">
   <!-- Horizontal Menu Start -->
   <nav id="navbar_main" class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav mx-md-auto"
      style="padding: 0.2rem 0 0.4rem;">
      <div class="container-fluid">
         <div class="offcanvas-header px-0">
            <a href="/" class="navbar-brand ms-3">

               <!--Logo start-->
               <div class="logo-main">
                  <div class="logo-normal">
                     <img src="{{ asset('assets/images/logos.png') }}"
                        alt="{{ getSetting('software_name') ?? config('app.name') }}" height="30">
                  </div>
               </div>
               <!--logo End-->
               <h4 class="logo-title ms-2">{{ getSetting('software_name') ?? config('app.name') }}</h4>
            </a>
            <button class="btn-close float-end"></button>
         </div>
         <ul class="navbar-nav">
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}"
                  href="{{ route('admin.dashboard') }}"> Dashboard </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}"
                  href="{{ route('admin.users.index') }}"> Users </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.packages.*') ? 'active' : '' }}"
                  href="{{ route('admin.packages.index') }}"> Packages </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.kyc.*') ? 'active' : '' }}"
                  href="{{ route('admin.kyc.index') }}"> KYC Requests </a>
            </li>
            <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle {{ Route::is('admin.deposits.*') || Route::is('admin.earnings') ? 'active' : '' }}"
                  href="#" id="transactionDropdown" role="button" data-bs-toggle="dropdown">
                  Transactions
               </a>
               <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="transactionDropdown">
                  <li><a class="dropdown-item fs-xs fw-bold" href="{{ route('admin.deposits.index') }}"><i class="fas fa-arrow-down me-2 opacity-50"></i>
                        Top-Up / Deposit Requests</a></li>
                  <li>
                     <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item fs-xs fw-bold" href="{{ route('admin.withdrawals.index') }}"><i
                           class="fas fa-arrow-up me-2 opacity-50"></i> Withdrawal Requests</a></li>
                  <li>
                     <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item fs-xs fw-bold" href="{{ route('admin.earnings') }}"><i
                           class="fas fa-hand-holding-dollar me-2 opacity-50"></i> Earnings / Incomes</a></li>
               </ul>
            </li>
         </ul>
      </div> <!-- container-fluid.// -->
   </nav>
   <!-- Sidebar Menu End -->
</div>