<?php
/* Template Name: Register Page */

get_header();

$user = wp_get_current_user();
$has_membership = false;

// Check for active Paid Member Subscriptions plan
if (function_exists('pms_get_member_subscriptions') && is_user_logged_in()) {
    $subscriptions = pms_get_member_subscriptions($user->ID);
    foreach ($subscriptions as $sub) {
        if ($sub->status === 'active') {
            $has_membership = true;
            break;
        }
    }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card p-4 shadow border-0">

        <?php if (is_user_logged_in() && $has_membership): ?>
          <h2 class="text-center mb-3">You're Already Subscribed</h2>
          <p class="text-center text-muted mb-4">You already have an active membership. Manage your subscription below:</p>
          <?php echo do_shortcode('[pms-account]'); ?>

        <?php elseif (is_user_logged_in()): ?>
          <h2 class="text-center mb-3">Complete Your Subscription</h2>
          <p class="text-center text-muted mb-4">You're logged in, but don’t have an active subscription yet. Choose a plan below:</p>
          <?php echo do_shortcode('[pms-subscription-plans]'); ?>

        <?php else: ?>
          <h2 class="text-center mb-3">Create Your Account</h2>
          <p class="text-center text-muted mb-4">Choose your membership plan and gain full access to exclusive insights and expert analysis.</p>
          <?php echo do_shortcode('[pms-register]'); ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
