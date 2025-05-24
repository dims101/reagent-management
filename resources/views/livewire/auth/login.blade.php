<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Login</h4>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" wire:model.defer="email" autocomplete="username"
                            value="{{ old('email') }}" required
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" wire:model.defer="password"
                            autocomplete="current-password" required
                            class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror

                        <div class="mt-1">
                            <a href="#" class="link-primary">Forgot Password?</a>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" wire:model.defer="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Login</button>
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
