import { select } from '@wordpress/data';

/**
 * 
 *
 * @return {boolean} True if the c...., false otherwise.
 */
export function getCurrentContextualPostId( postId=null ) {
	const post = postId || select('core/editor').getCurrentPostId();
	// console.log(post);

	return post;
}

