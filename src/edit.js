import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	AlignmentControl,
} from '@wordpress/block-editor';
import {
	ToolbarGroup,
	ToolbarButton,
	SelectControl,
	ToggleControl,
	TextControl,
	PanelBody,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const {
		dateSource = 'publish',
		showPrefix = true,
		linkTo = 'none',
		customField = '',
		numberFormat = 'label-number',
		align,
	} = attributes;

	const solLabel = __( 'Sol', 'mars-sol-date' );
	const postId = context.postId;
	const postType = context.postType;
	const post = useSelect(
		( select ) =>
			postId
				? select( 'core' ).getEntityRecord(
						'postType',
						postType,
						postId
				  )
				: null,
		[ postId, postType ]
	);
	const getDate = () => {
		if ( post ) {
			if ( dateSource === 'modified' && post.modified ) {
				return post.modified;
			} else if (
				dateSource === 'custom' &&
				customField &&
				post.meta &&
				post.meta[ customField ]
			) {
				return post.meta[ customField ];
			} else if ( post.date ) {
				return post.date;
			}
		}
		return null;
	};

	function getSolNumber( dateStr ) {
		if ( ! dateStr ) {
			return '';
		}
		const postTimestamp = new Date( dateStr ).getTime() / 1000;
		let firstPostTime = wp.data
			.select( 'core/editor' )
			.getEditedPostAttribute( 'date' );
		if ( ! firstPostTime && post ) {
			firstPostTime = post.date;
		}
		if ( ! firstPostTime ) {
			return '';
		}
		const firstTimestamp = new Date( firstPostTime ).getTime() / 1000;
		const solLength = 24 * 3600 + 39 * 60 + 35;
		return Math.floor( ( postTimestamp - firstTimestamp ) / solLength ) + 1;
	}

	const date = getDate();
	const solNumber = getSolNumber( date );
	let label = '';
	if ( numberFormat === 'label-number' ) {
		label = `${ showPrefix ? solLabel : '' } ${
			solNumber > 0 ? solNumber : ''
		}`.trim();
	} else if ( numberFormat === 'label' ) {
		label = `${ showPrefix ? solLabel : '' }`.trim();
	} else if ( numberFormat === 'number' ) {
		label = solNumber > 0 ? solNumber : '';
	}

	let display = label;
	if ( linkTo === 'post' && post && post.link ) {
		display = <a href={ post.link }>{ label }</a>;
	}

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="admin-links"
						label={ __( 'Link to post', 'mars-sol-date' ) }
						isActive={ linkTo === 'post' }
						onClick={ () =>
							setAttributes( {
								linkTo: linkTo === 'post' ? 'none' : 'post',
							} )
						}
					/>
				</ToolbarGroup>
				<AlignmentControl
					value={ align }
					onChange={ ( next ) => setAttributes( { align: next } ) }
				/>
			</BlockControls>
			<InspectorControls>
				<PanelBody
					title={ __( 'Mars Sol Date Settings', 'mars-sol-date' ) }
				>
					<SelectControl
						label={ __( 'Date Source', 'mars-sol-date' ) }
						value={ dateSource }
						options={ [
							{
								label: __( 'Published Date', 'mars-sol-date' ),
								value: 'publish',
							},
							{
								label: __( 'Last Modified', 'mars-sol-date' ),
								value: 'modified',
							},
							{
								label: __( 'Custom Field', 'mars-sol-date' ),
								value: 'custom',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { dateSource: value } )
						}
					/>
					{ dateSource === 'custom' && (
						<TextControl
							label={ __( 'Custom Field Name', 'mars-sol-date' ) }
							value={ customField }
							onChange={ ( value ) =>
								setAttributes( { customField: value } )
							}
						/>
					) }
					<ToggleControl
						label={ __( 'Show Prefix', 'mars-sol-date' ) }
						checked={ showPrefix }
						onChange={ () =>
							setAttributes( { showPrefix: ! showPrefix } )
						}
					/>
					<SelectControl
						label={ __( 'Sol Number Format', 'mars-sol-date' ) }
						value={ numberFormat }
						options={ [
							{
								label: __(
									'Label + Number (e.g. Sol 51)',
									'mars-sol-date'
								),
								value: 'label-number',
							},
							{
								label: __(
									'Number Only (e.g. 51)',
									'mars-sol-date'
								),
								value: 'number',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { numberFormat: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<span { ...useBlockProps( { style: { textAlign: align } } ) }>
				{ display }
			</span>
		</>
	);
}
