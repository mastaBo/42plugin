<div class="wrap">
    <h1><?php _e( 'Add Client', 'wedevs' ); ?></h1>

    <form action="" method="post">

        <table class="form-table">
            <tbody>
                <tr class="row-name">
                    <th scope="row">
                        <label for="name"><?php _e( 'Имя компании', 'wedevs' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" placeholder="<?php echo esc_attr( '', 'wedevs' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('То, как в компании принято называть клиента', 'wedevs' ); ?></span>
                    </td>
                </tr>
                <tr class="row-entity">
                    <th scope="row">
                        <label for="entity"><?php _e( 'Юр лицо', 'wedevs' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="entity" id="entity" class="regular-text" placeholder="<?php echo esc_attr( '', 'wedevs' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('Официальное наименование организации', 'wedevs' ); ?></span>
                    </td>
                </tr>
                <tr class="row-INN">
                    <th scope="row">
                        <label for="INN"><?php _e( 'ИНН', 'wedevs' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="INN" id="INN" class="regular-text" placeholder="<?php echo esc_attr( '', 'wedevs' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('ИНН организации', 'wedevs' ); ?></span>
                    </td>
                </tr>
                <tr class="row-description">
                    <th scope="row">
                        <label for="description"><?php _e( 'Описание', 'wedevs' ); ?></label>
                    </th>
                    <td>
                        <textarea name="description" id="description"placeholder="<?php echo esc_attr( '', 'wedevs' ); ?>" rows="5" cols="30"></textarea>
                    </td>
                </tr>
             </tbody>
        </table>

        <input type="hidden" name="field_id" value="0">

        <?php wp_nonce_field( 'client-new' ); ?>
        <?php submit_button( __( 'Add New Client', 'wedevs' ), 'primary', 'submit_client' ); ?>

    </form>
</div>