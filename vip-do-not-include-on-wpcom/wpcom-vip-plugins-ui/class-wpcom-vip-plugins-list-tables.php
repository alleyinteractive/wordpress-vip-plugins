<?php

class WPcom_VIP_Plugins_UI_List_Table extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'plural' => 'plugins',
		) );
	}

	public function prepare_items() {
		$active = $inactive = array();

		// The path has to be 
		foreach ( get_plugins( '/../themes/vip/plugins' ) as $plugin_file => $plugin_data ) {

			$plugin_folder = basename( dirname( $plugin_file ) );

			// FPP is listed separately
			if ( isset( WPcom_VIP_Plugins_UI()->fpp_plugins[ $plugin_folder ] ) )
				continue;

			$plugin_file = 'plugins/' . $plugin_file;

			$status = WPcom_VIP_Plugins_UI()->is_plugin_active( $plugin_folder ) ? 'active' : 'inactive';

			// Don't want some plugins showing up in the list
			if ( 'inactive' == $status && in_array( $plugin_folder, WPcom_VIP_Plugins_UI()->hidden_plugins ) )
				continue;

			// Translate, Don't Apply Markup, Sanitize HTML
			${$status}[$plugin_file] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
		}

		$this->items = array_merge( $active, $inactive );
	}

	public function no_items() {
		echo 'There was an error listing out plugins. Please try again in a bit.';
	}

	public function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}

	public function display_rows() {
		foreach ( $this->items as $plugin_file => $plugin_data )
			$this->single_row( $plugin_file, $plugin_data );
	}

	public function single_row( $plugin_file, $plugin_data ) {
		$plugin = basename( dirname( $plugin_file ) );

		$is_active = WPcom_VIP_Plugins_UI()->is_plugin_active( $plugin );

		$class = $is_active ? 'active' : 'inactive';
		if ( $is_active )
			$class .= ' active-' . $is_active;

		$actions = array();
		$actions = WPcom_VIP_Plugins_UI()->add_activate_or_deactive_action_link( $actions, $plugin );

		$plugin_name = $plugin_data['Name'];
		$description = '<p>' . ( $plugin_data['Description'] ? $plugin_data['Description'] : '&nbsp;' ) . '</p>';

		echo '<tr class="' . esc_attr( $class ) . '">';

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			switch ( $column_name ) {
				case 'name':
					echo "<td class='plugin-title'$style><strong>$plugin_name</strong>";
					echo $this->row_actions( $actions, true );
					echo "</td>";
					break;
				case 'description':
					echo "<td class='column-description desc'$style>
						<div class='plugin-description'>$description</div>
						<div class='$class second plugin-version-author-uri'>";

					$plugin_meta = array();

					$plugin_meta[] = '<a href="' . esc_url( 'http://lobby.vip.wordpress.com/plugins/' . $plugin . '/' ) . '" title="' . esc_attr__( 'Visit our VIP Lobby to learn more' ) . '">' . __( 'Learn More' ) . '</a>';

					if ( ! empty( $plugin_data['Author'] ) ) {
						$author = $plugin_data['Author'];
						if ( ! empty( $plugin_data['AuthorURI'] ) )
							$author = '<a href="' . $plugin_data['AuthorURI'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $plugin_data['Author'] . '</a>';
						$plugin_meta[] = sprintf( __( 'By %s' ), $author );
					}

					echo implode( ' | ', $plugin_meta );

					echo '</div></td>';
					break;
			}
		}

		echo "</tr>";
	}
}

class WPCOM_VIP_Featured_Plugins_List_Table extends WP_List_Table {

	/* We are using the backdoor _column_headers, because the columns filter is screen-specific
	 * but this is the second table on that screen and we can't differentiate between both.
	 *
	 * Setting this variable won't run the filter at all
	 */
	public $_column_headers = array( array( 'left' => '', 'right' => '' ), array(), array() );

	function __construct() {
		parent::__construct( array(
			'plural' => 'Featured Plugins',
		) );
	}

	public function prepare_items() {
		$counter = 0;
		$per_row = 2;

		$row = array(); // Temporary row-building container

		foreach ( WPcom_VIP_Plugins_UI()->fpp_plugins as $slug => $plugin ) {
			$counter++;

			$row[] = $slug;

			// If we have enough items per row, add $row to the items and start over
			if ( 0 == $counter % $per_row ) {
				$this->items[] = $row;
				$row = array();
			}
		}

		// Add any remainders
		if ( ! empty( $row ) )
			$this->items[] = $row;
	}

	public function column_default( $item, $column_name ) {
		if ( 'left' == $column_name && isset( $item[0] ) )
			$slug = $item[0];
		elseif ( 'right' == $column_name && isset( $item[1] ) )
			$slug = $item[1];
		else
			return;

		if ( ! isset( WPcom_VIP_Plugins_UI()->fpp_plugins[$slug] ) )
			return;

		$image_src = plugins_url( 'images/featured-plugins/' . $slug . '.png', __FILE__ );

		$lobby_url = 'http://lobby.vip.wordpress.com/plugins/' . $slug . '/';

		$actions = array();
		$actions = WPcom_VIP_Plugins_UI()->add_activate_or_deactive_action_link( $actions, $slug );
		$actions['learnmore'] = '<a href="' . esc_url( $lobby_url ) . '">Learn More</a>';

		ob_start();
?>
		<a href="<?php echo esc_url( $lobby_url ); ?>"><img src="<?php echo esc_url( $image_src ); ?>" width="48" height="48" /></a>
		<div class="description">
			<h3><a href="<?php echo esc_url( $lobby_url ); ?>"><?php echo WPcom_VIP_Plugins_UI()->fpp_plugins[$slug]['name']; ?></a></h3>
			<p><?php echo WPcom_VIP_Plugins_UI()->fpp_plugins[$slug]['description']; ?></p>
			<?php echo $this->row_actions( $actions, true ); ?>
		</div>
<?php
		return ob_get_clean();
	}

	public function print_column_headers( $with_id = true ) {
		if ( $with_id ) {
			echo '<th colspan="2">Featured Partners</th>';
		}
	}
}

?>