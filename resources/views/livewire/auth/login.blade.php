<div>
    <form wire:submit.prevent="login">
        <div>
            <label for="nup">NUP</label>
            <input type="text" id="nup" wire:model="nup" autocomplete="username" value="{{ old('nup') }}"
                pattern="[A-Za-z0-9]*" title="Only letters and numbers are allowed" required>
            <div>
                <a href="#">Forgot Password?</a>
            </div>
            @error('nup')
                <span>{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" wire:model="password" autocomplete="current-password" required>
            @error('password')
                <span>{{ $message }}</span>
            @enderror
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
        @if (session()->has('error'))
            <div>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </form>
</div>
