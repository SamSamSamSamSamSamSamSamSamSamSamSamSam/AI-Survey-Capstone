<?php $__env->startSection('title', 'Sign Up'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Create an Account</h3>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form id="signupForm" action="<?php echo e(route('signup.submit')); ?>" method="POST" novalidate>
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                <div class="invalid-feedback" id="passwordError" style="display:none;">
                    Passwords do not match.
                </div>
            </div>

            <div class="mb-3">
                <label for="role">Role</label>
                <select name="role" class="form-control" required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>

        <div class="mt-3 text-center">
            <p>Already have an account? <a href="<?php echo e(route('login')); ?>">Login here</a></p>
        </div>
    </div>
</div>


<script>
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value.trim();
    const confirm = document.getElementById('password_confirmation').value.trim();
    const errorDiv = document.getElementById('passwordError');

    if (password !== confirm) {
        e.preventDefault(); // stop form submission
        errorDiv.style.display = 'block';
        document.getElementById('password_confirmation').classList.add('is-invalid');
    } else {
        errorDiv.style.display = 'none';
        document.getElementById('password_confirmation').classList.remove('is-invalid');
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\arjoy\Desktop\DESKTOP\Capstone\AI-Survey-Capstone\laravel-front\resources\views/auth/signup.blade.php ENDPATH**/ ?>