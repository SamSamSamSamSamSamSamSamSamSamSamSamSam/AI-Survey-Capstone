<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-0">
    <!-- Main Auth Container -->
    <div class="auth-page d-flex min-vh-100 align-items-center justify-content-center">
        <div class="p-4" style="max-width: 1000px; width: 100%;">
            
            <!-- Auth Card -->
            <div class="card auth-card overflow-hidden">
                <div class="row g-0">
                    
                    <!-- Left Side - Welcome Section -->
                    <div class="col-lg-5 d-none d-lg-flex left-side align-items-center justify-content-center">
                        <div class="left-side-text text-center">
                            <h3 class="welcome-msg mb-2">Good to see you!</h3>
                            <p>Ready to evaluate?</p>
                        </div>
                    </div>

                    <!-- Right Side - Login Form -->
                    <div class="col-12 col-lg-7 right-side bg-white">
                        <div class="d-flex flex-column h-100">
                            
                            <!-- Logo Section -->
                            <div class="pt-5 mb-3 text-center">
                                <a href="javascript:void(0)" class="d-block auth-logo">
                                    <img src="<?php echo e(asset('assets/images/dcismicon.png')); ?>" alt="Logo" width="130">
                                </a>
                            </div>

                            <!-- Auth Content -->
                            <div class="auth-content my-auto p-4 p-md-5">
                                <div class="form-container slide-left" id="formContainer">
                                    <div class="form" id="form1">
                                        
                                        <!-- Header -->
                                        <div class="text-center">
                                            <h4 class="mt-2 text-dark">Sign in to continue.</h4>
                                        </div>

                                        <!-- Session Messages -->
                                        <?php if(session('success')): ?>
                                            <div class="alert alert-success mt-4" role="alert">
                                                <?php echo e(session('success')); ?>

                                            </div>
                                        <?php endif; ?> 
                                        
                                        <?php if(session('error')): ?>
                                            <div class="alert alert-danger mt-4" role="alert">
                                                <?php echo e(session('error')); ?>

                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Login Form -->
                                        <form class="mt-4 pt-2" method="POST" action="<?php echo e(route('login.submit')); ?>">
                                            <?php echo csrf_field(); ?>
                                            
                                            <!-- Email Field -->
                                            <div class="mb-3">
                                                <label class="form-label" for="email">Email</label>
                                                <input type="email" 
                                                       class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                       name="email" 
                                                       id="email" 
                                                       placeholder="Enter Your Email" 
                                                       value="<?php echo e(old('email')); ?>"
                                                       required>
                                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback d-block">
                                                        <?php echo e($message); ?>

                                                    </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            
                                            <!-- Password Field -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-grow-1">
                                                        <label class="form-label" for="password">Password</label>
                                                    </div>
                                                    
                                                    
                                                </div>
                                                
                                                <div class="input-group auth-pass-inputgroup">
                                                    <input type="password" 
                                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                           name="password" 
                                                           id="password" 
                                                           placeholder="Enter Your Password" 
                                                           required>
                                                    <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon">
                                                        <i class="mdi mdi-eye-outline"></i>
                                                    </button>
                                                </div>
                                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback d-block">
                                                        <?php echo e($message); ?>

                                                    </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <!-- Remember Me Checkbox -->
                                            <div class="row mb-4">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               id="remember-check" 
                                                               name="remember">
                                                        <label class="form-check-label" for="remember-check">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="mb-3">
                                                <button class="btn btn-sign-in w-100 waves-effect waves-light" type="submit">
                                                    Sign In
                                                </button>
                                            </div>
                                            <div class="text-center mt-3">
                                                <p class="mb-0">
                                                    Don't have an account?
                                                    <a href="<?php echo e(route('signup')); ?>" class="text-primary fw-semibold">
                                                        Sign Up
                                                    </a>
                                                </p>
                                            </div>
                                        </form>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Auth Card -->
            
        </div>
    </div>
    <!-- End Main Auth Container -->
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\arjoy\Desktop\DESKTOP\Capstone\AI-Survey-Capstone\laravel-front\resources\views/auth/login.blade.php ENDPATH**/ ?>