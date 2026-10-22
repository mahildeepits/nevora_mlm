<div class="navbar dual-horizontal">
   <!-- Horizontal Menu Start -->
   <nav id="navbar_main" class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav mx-md-auto"
      style="padding: 0.2rem 0 0.4rem;">
      <div class="container-fluid">
         <div class="offcanvas-header px-0">
            <a href="{{ route('admin.dashboard') }}" class="navbar-brand ms-3">
               <div class="logo-main d-flex align-items-center gap-2">
                  <img src="{{ asset('assets/images/logo-icon.png') }}" alt="NEVORA" style="width: 28px; height: 28px; object-fit: contain;">
                  <h4 class="logo-title ms-2 fw-bold text-primary mb-0">NEVORA ADMIN</h4>
               </div>
            </a>
            <button class="btn-close float-end"></button>
         </div>
         <ul class="navbar-nav">
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.dashboard*') ? 'active' : '' }}"
                  href="{{ route('admin.dashboard') }}">
                  <i class="fa-solid fa-chart-line me-1"></i> Dashboard
               </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.users*') ? 'active' : '' }}"
                  href="{{ route('admin.users.index') }}">
                  <i class="fa-solid fa-users me-1"></i> Members
               </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.slots*') ? 'active' : '' }}"
                  href="{{ route('admin.slots.index') }}">
                  <i class="fa-solid fa-cubes me-1"></i> 12 Dynamic Slots
               </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.transactions*') ? 'active' : '' }}"
                  href="{{ route('admin.transactions.index') }}">
                  <i class="fa-solid fa-receipt me-1"></i> Unified Ledger
               </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.announcements*') ? 'active' : '' }}"
                  href="{{ route('admin.announcements.index') }}">
                  <i class="fa-solid fa-bullhorn me-1"></i> Announcements
               </a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ Route::is('admin.settings*') ? 'active' : '' }}"
                  href="{{ route('admin.settings.index') }}">
                  <i class="fa-solid fa-gears me-1"></i> Settings
               </a>
            </li>
         </ul>
      </div>
   </nav>
   <!-- Sidebar Menu End -->
</div>
