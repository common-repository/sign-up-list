<?php 
namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This code performs the serverside rendering of the Gutenberg block sul-entries.
 * It displays the entries on the sign-up listy and related metadata.
 * What is displayed, depends on the option for publicvisibilty.
 * The display style depends on the option for style.
 */
$entries = Database::get_entries();
$options = get_option( 'sul_general_admin' );
$entries_count = count($entries);
$entries_left = max( ( $options['max_entries'] - $entries_count ), 0 );
$css_style = $options['style'].' sul-entries-wrapper';
?>
<div <?php echo get_block_wrapper_attributes( ['class' => $css_style ] ); ?>>
	<table id="sul-entries">
		<tbody>
		<?php if ( $options['publicvisibility'] != 'invisible' ) {
			foreach ( $entries as $entry ) {
			?>
			<tr>
				<td><?php
					switch ( $options['publicvisibility'] ) {
						case 'fullname': 
							echo esc_html( $entry['firstname']).' '.esc_html( $entry['lastname'] );
							break;
						case 'firstname': 
							echo esc_html( $entry['firstname']);
							break;
						case 'fullname_extra':
							if ( empty( $entry['extra_1'] ) ) {
								echo esc_html( $entry['firstname']).' '.esc_html( $entry['lastname'] );
							} else {
								echo esc_html( $entry['firstname']).' '.esc_html( $entry['lastname'].' ('.$entry['extra_1'].')' );
							}
							break;
						case 'firstname_extra': 
							if ( empty( $entry['extra_1'] ) ) {
								echo esc_html( $entry['firstname']);
							} else {
								echo esc_html( $entry['firstname'].' ('.$entry['extra_1'].')' );
							}
							break;
						default:
							echo esc_html( 'No entries' );
					}
					?>
				</td>
			</tr>
			<?php
			}
		} 
		?>
		</tbody>
		<tfoot>
			<tr>
				<?php 
				switch( $entries_left ) {
					case 0:
						$footer = __( 'There are no spaces left', 'sign-up-list' );
						break;
					case 1:
						$footer = __( 'Only', 'sign-up-list' ).' '.$entries_left.' '.__('space left', 'sign-up-list');
						break;
					default:
						$footer = $entries_left.' '.__( 'spaces left', 'sign-up-list' );
				}
				?>
				<td><?php echo esc_html( $footer ); ?></span></td>
			</tr>
		</tfoot>
	</table>
</div>
