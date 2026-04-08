<nav aria-label="Global" class="mx-auto flex items-center justify-between backdrop:bg-base-100/30 backdrop:backdrop-blur-sm p-6 lg:px-8">
    <div class="flex lg:flex-1">
      <a href="/" class="-m-1.5 p-1.5">
        <span class="sr-only">Q Square Martial Arts Competition</span>
        <img src="{{ Vite::asset('resources/assets/logo.png') }}" alt="Logo" class="h-15 w-auto" />
      </a>
    </div>
    <div class="flex lg:hidden">
      <button type="button" command="show-modal" commandfor="mobile-menu" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-base-content"">
        <span class="sr-only">Open main menu</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
          <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
    <el-popover-group class="hidden lg:flex lg:gap-x-12">
      <a href="/" class="text-sm/6 font-semibold text-base-content"">Beranda</a>
      <a href="#" class="text-sm/6 font-semibold text-base-content"">Data Peserta</a>
      <a href="#" class="text-sm/6 font-semibold text-base-content"">Pertandingan</a>
    </el-popover-group>
    @if (Route::has('login'))
    <div class="hidden lg:flex lg:flex-1 lg:justify-end">
      @auth
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button class="flex item-center gap-x-1 text-sm/6 font-semibold text-error mr-6">
                Keluar
            </button>
        </form>
        <a href="{{ url('/admin/dashboard') }}" class="text-sm/6 font-semibold text-base-content"">Dashboard <span aria-hidden="true">&rarr;</span></a>
      @else
        <a href="{{ route('register') }}" class="text-sm/6 font-semibold text-base-content"">Masuk <span aria-hidden="true">&rarr;</span></a>
        @endauth
    </div>
    @endif
  </nav>
  <el-dialog>
    <dialog id="mobile-menu" class="backdrop:bg-base-100/30 backdrop:backdrop-blur-sm lg:hidden">
      <div tabindex="0" class="fixed inset-0 focus:outline-none">
        <el-dialog-panel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-base-200 p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-100/10">
          <div class="flex items-center justify-between">
            <a href="/" class="-m-1.5 p-1.5">
              <span class="sr-only">Q Square Martial Arts Competition</span>
              <img src="{{ Vite::asset('resources/assets/logo.png') }}" alt="Logo" class="h-15 w-auto" />
            </a>
            <button type="button" command="close" commandfor="mobile-menu" class="-m-2.5 rounded-md p-2.5 text-gray-400">
              <span class="sr-only">Close menu</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-base-content/10">
              <div class="space-y-2 py-6">
                <a href="/" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-base-content">Beranda</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-base-content">Data Peserta</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-base-content">Pertandingan</a>
              </div>
              @if (Route::has('login'))
              <div class="py-6">
                @auth
                  <a href="{{ url('/admin/dashboard') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-base-content">Dashboard</a>
                @else
                  <a href="{{ route('register') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-base-content">Masuk</a>
                @endauth
              </div>
              @endif
            </div>
          </div>
        </el-dialog-panel>
      </div>
    </dialog>
  </el-dialog>