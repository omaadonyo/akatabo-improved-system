<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky :collapsible="true" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @can('manage-customers')
                        <flux:sidebar.item icon="users" :href="route('customers')" :current="request()->routeIs('customers')" wire:navigate>
                            {{ __('Customers') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('manage-inventory')
                        <flux:sidebar.item icon="box" :href="route('inventory')" :current="request()->routeIs('inventory')" wire:navigate>
                            {{ __('Inventory') }}
                        </flux:sidebar.item>
                    @endcan

                    <flux:sidebar.item icon="chart-bar" :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>

                    @can('manage-users')
                        <flux:sidebar.item icon="user-group" :href="route('users')" :current="request()->routeIs('users')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('activity-logs')" :current="request()->routeIs('activity-logs')" wire:navigate>
                            {{ __('Activity Logs') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Sales')" class="grid">
                    <flux:sidebar.item icon="file-text" :href="route('quotations')" :current="request()->routeIs('quotations*')" wire:navigate>
                        {{ __('Quotations') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="file-invoice" :href="route('invoices')" :current="request()->routeIs('invoices*')" wire:navigate>
                        {{ __('Invoices') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="credit-card" :href="route('payments')" :current="request()->routeIs('payments')" wire:navigate>
                        {{ __('Payments') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="building-office" :href="route('rentals')" :current="request()->routeIs('rentals')" wire:navigate>
                        {{ __('Rentals') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" :href="route('customer-quotations')" :current="request()->routeIs('customer-quotations')" wire:navigate>
                        {{ __('Customer Requests') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Settings')" class="grid">
                    @can('manage-business')
                        <flux:sidebar.item icon="building" :href="route('business.edit')" :current="request()->routeIs('business.edit')" wire:navigate>
                            {{ __('Business') }}
                        </flux:sidebar.item>
                    @endcan

                    <flux:sidebar.item icon="server-stack" :href="route('backup.edit')" :current="request()->routeIs('backup.edit')" wire:navigate>
                        {{ __('Backup') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                        {{ __('Profile') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/omaadonyo/akatabo-improved-system" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="{{ url('docs/index.html') }}" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            @php $biz = activeBusiness(); $bizList = auth()->user()->businesses; @endphp
            <flux:dropdown position="bottom" align="start">
                <flux:button variant="ghost" class="cursor-pointer" size="sm">
                    <flux:icon name="building" variant="micro" class="size-3.5" />
                    <span class="max-w-24 truncate">{{ $biz?->name ?? __('No business') }}</span>
                </flux:button>

                <flux:menu>
                    @foreach ($bizList as $b)
                        <form method="POST" action="{{ route('business.switch', $b) }}">
                            @csrf
                            <flux:menu.item as="button" type="submit" class="w-full cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-5 items-center justify-center rounded bg-neutral-200 text-[10px] font-semibold dark:bg-neutral-700">
                                        {{ substr($b->name, 0, 1) }}
                                    </div>
                                    <span>{{ $b->name }}</span>
                                    @if ($b->id === activeBusinessId())
                                        <flux:icon name="check" variant="micro" class="ms-auto text-primary-500" />
                                    @endif
                                </div>
                            </flux:menu.item>
                        </form>
                    @endforeach
                    <flux:menu.separator />
                    <flux:menu.item :href="route('onboarding', ['add' => 1])" icon="plus" wire:navigate>
                        {{ __('Add Business') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        @can('manage-business')
                        <flux:menu.item :href="route('business.edit')" icon="building" wire:navigate>
                            {{ __('Business') }}
                        </flux:menu.item>
                    @endcan

                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Desktop Header -->
        <flux:header class="hidden lg:flex">
            <flux:sidebar.toggle icon="bars-2" inset="left" />

            @php $biz = activeBusiness(); $bizList = auth()->user()->businesses; @endphp
            <flux:dropdown position="bottom" align="start">
                <flux:button variant="ghost" class="cursor-pointer" size="sm">
                    <flux:icon name="building" variant="micro" class="size-3.5" />
                    <span>{{ $biz?->name ?? __('No business') }}</span>
                </flux:button>

                <flux:menu>
                    @foreach ($bizList as $b)
                        <form method="POST" action="{{ route('business.switch', $b) }}">
                            @csrf
                            <flux:menu.item as="button" type="submit" class="w-full cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-5 items-center justify-center rounded bg-neutral-200 text-[10px] font-semibold dark:bg-neutral-700">
                                        {{ substr($b->name, 0, 1) }}
                                    </div>
                                    <span>{{ $b->name }}</span>
                                    @if ($b->id === activeBusinessId())
                                        <flux:icon name="check" variant="micro" class="ms-auto text-primary-500" />
                                    @endif
                                </div>
                            </flux:menu.item>
                        </form>
                    @endforeach
                    <flux:menu.separator />
                    <flux:menu.item :href="route('onboarding', ['add' => 1])" icon="plus" wire:navigate>
                        {{ __('Add Business') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <flux:spacer />
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
