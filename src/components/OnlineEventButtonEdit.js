/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import { moreHorizontalMobile } from '@wordpress/icons';

import { useState, useCallback, useMemo } from '@wordpress/element';
/**
 * This component shows ...
 * 
 * @param {Object} props Properties of the 'gp-onlineevent-or-venue'-core/button block-variation.
 * @returns ... component with a range slider to set url_shorten
 */
const OnlineEventButtonEdit = (props=null) => {

    const setValue = () => {
		return props?.attributes?.urlShorten || 35
	}

    const update = useCallback( (value) => { 

        const newAttributes = {
            ...props.attributes,
            urlShorten: value,
        };
        props.setAttributes(newAttributes);
    }, [props] );

    return (
		<>
            <RangeControl
                label={__('Maximum length of the shortened URL.', 'gatherpress')}
                help={__('Defaults to 35 characters.', 'gatherpress')}
                // initialPosition={35}
                value={ setValue() }
                resetFallbackValue={35}
                withInputField={false}
                afterIcon={ moreHorizontalMobile }
                // marks
                max={100}
                min={0}
                // onBlur={function noRefCheck(){}}
                // onChange={function noRefCheck(){}}
                // onChange={() => {}}
                onChange={ update }
                // onFocus={function noRefCheck(){}}
                // onMouseLeave={function noRefCheck(){}}
                // onMouseMove={function noRefCheck(){}}
                step={1}
            />
		</>
		
	);
};

export { OnlineEventButtonEdit };

