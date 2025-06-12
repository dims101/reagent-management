{{-- filepath: resources/views/livewire/auth/login.blade.php --}}

@section('title', 'Login')
@section('content')
    <div class="login-bg d-flex justify-content-center align-items-center " style="min-height: 100vh;">
        <div class="col-md-3">
            <div class="card px-4 py-4" style="border-radius: 15px; box-shadow: 0 4px 32px rgba(0,0,0,0.10); border: none;">
                <div class="card-body ">
                    <div class="text-center mb-4 mt-2">
                        <img src="{{ asset('assets/img/lai/logo.png') }}" alt="Logo" class="img-fluid"
                            style="max-height: 150px;">
                    </div>
                    <form class="px-3" wire:submit.prevent="login">
                        <div class="mb-3">
                            <label for="nup" class="form-label">NUP</label>
                            <input type="text" id="nup" wire:model.defer="nup" autocomplete="username"
                                value="{{ old('nup') }}" required class="form-control @error('nup') is-invalid @enderror"
                                placeholder="Enter your NUP">
                            @error('nup')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" wire:model.defer="password"
                                autocomplete="current-password" required
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password">
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100"
                                style="height: 45px; font-size: 1.1rem;">Log in</button>
                        </div>
                        <div class="mb-4 pb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check" style="margin-bottom: 0;">
                                    <input type="checkbox" class="form-check-input" id="remember"
                                        wire:model.defer="remember">
                                    <label class="form-check-label" for="remember" style="font-weight: normal;">Remember
                                        me</label>
                                </div>
                                {{-- <a href="{{ route('password.request') }}" class="link-primary text-decoration-none"
                                    style="font-size: 1rem;">Forgot
                                    your password?</a> --}}
                            </div>
                        </div>

                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
