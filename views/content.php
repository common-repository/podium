<div id="podium">

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
      <input name="action" type="hidden" value="podium_script_code">

      <div class="container">

        <div class="card">

          <div class="card-header">
            <img src="<?php echo esc_url(PODIUM_DIR_URL . 'assets/podium-logo.svg') ?>" alt="Podium Logo" />
          </div>

          <div class="card-body">
            <?php if ( ! empty($podium_script_code) ) {  ?>
              
              <?php if ( $podium_installation == false ) { ?>
                <p class="title">Connect your website to Podium</p>
                <p class="message">
                  You're just a click away from capturing new leads directly from your
                  website!
                </p>
                <input name="connect" type="hidden" value="true">
                <?php submit_button('Connect', 'connect-btn'); ?>

              <?php } else { ?>
                <img src="<?php echo esc_url(PODIUM_DIR_URL . 'assets/connected-icon.svg') ?>" class="icon" alt="Connected" />
                <p class="title">Connected!</p>
                <p class="message">
                  Your website is connected to Podium, and your website visitors
                  will be able to engage with the Website Tools you've activated.
                </p>

              <?php } ?>

            <?php } else { ?>
              <p class="title">Connect your website to Podium</p>
              <p class="message">
                Please inform the token:
              </p>
              <input 
                    name="user-organization-token" 
                    type="text" 
                    placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                    autocomplete="off"
                    class="input-token"
              />
              <?php submit_button('Connect', 'connect-btn'); ?>

            <?php } ?>
          </div>

        </div>

        <p class="sign-in-text">
          To connect to Podium, <a href="#">sign in</a> to your account or
          <a href="#">get started for free</a> today.
        </p>

      </div>

  </form>

</div>