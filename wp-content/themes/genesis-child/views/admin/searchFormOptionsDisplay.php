<?php
/**
	Search Form Values form
*/

?>

<h1>Search Form Values</h1>
<form method="post" action="options.php">
    <?php
        settings_fields( 'searchForm' );
        do_settings_sections( 'searchForm' );
        $searchFormValue	= get_option( 'searchInputFieldValue' ); 
    ?>
    <fieldset>
        <p class="disp_bar_message hidden message">Social Icon Bar will not be displayed without at least one position selected.</p>
        <p class="disp_soc_message hidden message">Social Icon Bar will not be displayed without at least one social network selected.</p>
        <p>
            Display Social Icon Bar:<br />
            <label for="searchInputFieldValue">String to appear in Search Form input field </label><input type="text" value="<?php echo $searchFormValue; ?> " name="searchInputFieldValue" />
        </p>
    </fieldset>
    <?php submit_button(); ?>
</form>

