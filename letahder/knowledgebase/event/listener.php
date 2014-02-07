<?php

namespace letahder\knowledgebase\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	protected $helper;
	protected $template;
	protected $user;
	protected $kb_categories_table;
	protected $kb_articles_table;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $kb_categories_table, $kb_articles_table)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->kb_categories_table = $kb_categories_table;
		$this->kb_articles_table = $kb_articles_table;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'	=> 'user_setup',
			'core.permissions'	=> 'permissions',
			'core.page_header'	=> 'page_header',
		);
	}

	public function user_setup($event)
	{
		$this->user->add_lang_ext('letahder/knowledgebase', 'kb');
	}

	public function permissions($event)
	{
		$categories = $event['categories'];
		$categories = array_merge($categories, array('kb' => 'ACL_CAT_KB'));
		$event['categories'] = $categories;

		$permissions = $event['permissions'];
		$permissions = array_merge($permissions, array(
			'u_kb_delete'	=> array('lang' => 'ACL_U_KB_DELETE', 'cat' => 'kb'),
			'u_kb_edit'		=> array('lang' => 'ACL_U_KB_EDIT', 'cat' => 'kb'),
			'u_kb_post'		=> array('lang' => 'ACL_U_KB_POST', 'cat' => 'kb'),

			'm_kb_approve'		=> array('lang' => 'ACL_M_KB_APPROVE', 'cat' => 'kb'),
			'm_kb_delete'		=> array('lang' => 'ACL_M_KB_DELETE', 'cat' => 'kb'),
			'm_kb_deny'			=> array('lang' => 'ACL_M_KB_DENY', 'cat' => 'kb'),
			'm_kb_disapprove'	=> array('lang' => 'ACL_M_KB_DISAPPROVE', 'cat' => 'kb'),
			'm_kb_edit'			=> array('lang' => 'ACL_M_KB_EDIT', 'cat' => 'kb'),

			'a_kb_manage'	=> array('lang' => 'ACL_A_KB_MANAGE', 'cat' => 'kb'),
		));
		$event['permissions'] = $permissions;
	}

	public function page_header($event)
	{
		$this->template->assign_vars(array(
			'U_KNOWLEDGE_BASE' => $this->helper->url('kb/index'),
		));
	}
}
