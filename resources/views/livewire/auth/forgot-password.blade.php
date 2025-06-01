@section('title', 'Forgot Password')
@section('content')
    <div class="login-bg d-flex justify-content-center align-items-center " style="min-height: 100vh;">
        <div class="col-md-3">
            <div class="card px-4 py-4" style="border-radius: 15px; box-shadow: 0 4px 32px rgba(0,0,0,0.10); border: none;">
                <div class="card-body ">
                    <div class="text-center mb-4 mt-2">
                        <img src="{{ asset('assets/img/lai/logo.png') }}" alt="Logo" class="img-fluid"
                            style="max-height: 150px;">
                    </div>
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form wire:submit="sendResetLink">
                        <div class="form-group">
                            <label for="email">Enter your email address :</label>
                            <input type="email" class="form-control mt-2 @error('email') is-invalid @enderror"
                                id="email" wire:model.live.debounce.500ms="email" required autofocus
                                placeholder="Enter your email">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-3" wire:loading.attr="disabled">
                            Send Password Reset Link
                        </button>
                        <div class="text-center mt-3 ">
                            <a class="link-primary text-decoration-none" href="{{ route('login') }}">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
