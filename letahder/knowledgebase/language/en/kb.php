<?php
/**
*
* kb [English]
*
* @package language
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

// ACP vars
$lang = array_merge($lang, array(
	'ACP_KNOWLEDGE_BASE_ADD_CATEGORY'				=> 'Add Category',
	'ACP_KNOWLEDGE_BASE_CATEGORIES'					=> 'Manage Categories',
	'ACP_KNOWLEDGE_BASE_CATEGORY_ADDED'				=> 'Category added successfully.',
	'ACP_KNOWLEDGE_BASE_CATEGORY_DELETED'			=> 'Category deleted successfully.',
	'ACP_KNOWLEDGE_BASE_CATEGORY_EDITED'			=> 'Category updated successfully.',
	'ACP_KNOWLEDGE_BASE_CATEGORY_NAME'				=> 'Category Name',
	'ACP_KNOWLEDGE_BASE_CATEGORY_OPTIONS'			=> 'Options',
	'ACP_KNOWLEDGE_BASE_DELETE_ARTICLES'			=> 'Delete articles',
	'ACP_KNOWLEDGE_BASE_DELETE_CATEGORY'			=> 'Delete Category',
	'ACP_KNOWLEDGE_BASE_DELETE_CATEGORY_CONFIRM'	=> 'Are you sure you want to delete this category?',
	'ACP_KNOWLEDGE_BASE_EDIT_CATEGORY'				=> 'Edit Category',
	'ACP_KNOWLEDGE_BASE_MOVE_ARTICLES'				=> 'Move articles',
	'ACP_KNOWLEDGE_BASE_TITLE'						=> 'Knowledge Base',
));

// Common vars
$lang = array_merge($lang, array(
	'ALL_CATEGORIES'	=> 'All categories',
	'APPROVE'			=> 'Approve',
	'APPROVE_CONFIRM'	=> 'Are you sure you want to approve this article?',

	'BUTTON_NEW_ARTICLE'	=> 'New Article',

	'CANNOT_APPROVE'	=> 'You cannot approve an article that is already approved.',
	'CANNOT_DENY'		=> 'You cannot deny an article that is already denied.',
	'CANNOT_DISAPPROVE'	=> 'You cannot disapprove an article that is already disapproved.',

	'DELETE_CONFIRM'		=> 'Are you sure you want to delete this article?',
	'DENY_CONFIRM'			=> 'Are you sure you want to deny this article?',
	'DISAPPROVE'			=> 'Disapprove',
	'DISAPPROVE_CONFIRM'	=> 'Are you sure you want to disapprove this article?',

	'KNOWLEDGE_BASE'	=> 'Knowledge Base',

	'NO_ARTICLE'	=> 'The requested article does not exist.',
	'NO_ARTICLES'	=> 'No articles',
	'NO_CATEGORIES'	=> 'No categories',

	'POST_ARTICLE'	=> 'Post a new article',

	'RETURN_ARTICLE'	=> 'Return',
));

// Permissions
$lang = array_merge($lang, array(
	'ACL_CAT_KB'	=> 'Knowledge Base',

	'ACL_U_KB_DELETE'	=> 'Can delete own articles',
	'ACL_U_KB_EDIT'		=> 'Can edit own articles',
	'ACL_U_KB_POST'		=> 'Can post new articles',

	'ACL_M_KB_APPROVE'		=> 'Can approve articles',
	'ACL_M_KB_DELETE'		=> 'Can delete articles',
	'ACL_M_KB_DENY'			=> 'Can deny articles',
	'ACL_M_KB_DISAPPROVE'	=> 'Can disapprove articles',
	'ACL_M_KB_EDIT'			=> 'Can edit articles',

	'ACL_A_KB_MANAGE'	=> 'Can manage Knowledge Base',
));
