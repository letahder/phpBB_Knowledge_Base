<?php

namespace letahder\knowledgebase\controller;

class main
{
	protected $auth;
	protected $db;
	protected $helper;
	protected $request;
	protected $template;
	protected $user;
	protected $kb_categories_table;
	protected $kb_articles_table;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver $db, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $kb_categories_table, $kb_articles_table)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->kb_categories_table = $kb_categories_table;
		$this->kb_articles_table = $kb_articles_table;
	}

	public function index()
	{
		$category_id = $this->request->variable('c', 'all');
		$type = $this->request->variable('type', 'approved');

		$sql = 'SELECT *
			FROM ' . $this->kb_categories_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		$category_id_array = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$category_id_array[] .= $row['category_id'];

			$this->template->assign_block_vars('categories', array(
				'CATEGORY_ID'	=> $row['category_id'],
				'CATEGORY_NAME'	=> $row['category_name'],
				'SELECTED'		=> ($row['category_id'] == $category_id) ? ' selected="selected"' : '',
			));
		}
		$this->db->sql_freeresult($result);

		if (!in_array($category_id, $category_id_array) && $category_id != 'all')
		{
			trigger_error('Invalid category');
		}

		$sql_where = '';
		if (($this->auth->acl_get('m_kb_approve') || $this->auth->acl_get('m_kb_deny')) && $type)
		{
			switch ($type)
			{
				case 'approved':
					$sql_where .= ' AND article_approved = 1';
				break;

				case 'disapproved':
					$sql_where .= ' AND article_approved = 0 AND article_denied = 0';
				break;

				case 'denied':
					$sql_where .= ' AND article_approved = 0 AND article_denied = 1';
				break;
			}
		}
		else
		{
			$sql_where .= ' AND article_approved = 1';
		}

		$sql_where .= ($category_id != 'all') ? ' AND a.category_id = ' . (int) $category_id : '';

		$sql = 'SELECT a.*, c.*, u.user_id, u.user_colour, u.username
			FROM ' . $this->kb_articles_table . ' a, ' . $this->kb_categories_table . ' c, ' . USERS_TABLE . " u
			WHERE u.user_id = a.article_poster
				AND a.category_id = c.category_id
				$sql_where";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('article_row', array(
				'ARTICLE_TITLE'			=> $row['article_title'],
				'ARTICLE_DESCRIPTION'	=> $row['article_description'],
				'ARTICLE_POSTSER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'ARTICLE_CATEGORY'		=> $row['category_name'],
				'ARTICLE_TIME'			=> $this->user->format_date($row['article_time']),

				'U_VIEW_ARTICLE'	=> $this->helper->url('kb/viewarticle', 'a=' . $row['article_id']),
			));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'S_POST_NEW_ARTICLE'	=> ($this->auth->acl_get('u_kb_post')) ? true : false,

			'U_POST_NEW_ARTICLE'	=> $this->helper->url('kb/posting', 'mode=post'),
		));

		page_header($this->user->lang['KNOWLEDGE_BASE']);

		return $this->helper->render('kb_index_body.html');

		page_footer();
	}

	public function mcp()
	{
		$article_id = $this->request->variable('a', 0);
		$mode = $this->request->variable('mode', '');

		if (!$article_id)
		{
			trigger_error('NO_ARTICLE');
		}

		$sql = 'SELECT *
			FROM ' . $this->kb_articles_table . '
			WHERE article_id = ' . (int) $article_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('NO_ARTICLE');
		}

		switch ($mode)
		{
			case 'approve':
				if (!$this->auth->acl_get('m_kb_approve'))
				{
					trigger_error('NOT_AUTHORISED');
				}

				if ($row['article_approved'])
				{
					trigger_error('CANNOT_APPROVE');
				}

				if (confirm_box(true))
				{
					$sql_array = array(
						'article_approved'	=> 1,
						'article_denied'	=> 0,
					);

					$sql = 'UPDATE ' . $this->kb_articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE article_id = ' . (int) $article_id;
					$this->db->sql_query($sql);
				}
				else
				{
					confirm_box(false, $this->user->lang['APPROVE_CONFIRM']);
				}

				redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
			break;

			case 'delete':
				if (!$this->auth->acl_get('m_kb_delete'))
				{
					trigger_error('NOT_AUTHORISED');
				}

				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $this->kb_articles_table . ' WHERE article_id = ' . (int) $article_id;
					$this->db->sql_query($sql);

					redirect($this->helper->url('kb/index'));
				}
				else
				{
					confirm_box(false, $this->user->lang['DELETE_CONFIRM']);

					redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
				}
			break;

			case 'deny':
				if (!$this->auth->acl_get('m_kb_deny'))
				{
					trigger_error('NOT_AUTHORISED');
				}

				if ($row['article_denied'])
				{
					trigger_error('CANNOT_DENY');
				}

				if (confirm_box(true))
				{
					$sql_array = array(
						'article_approved'	=> 0,
						'article_denied'	=> 1,
					);

					$sql = 'UPDATE ' . $this->kb_articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE article_id = ' . (int) $article_id;
					$this->db->sql_query($sql);

					if (!this->auth->acl_get('m_kb_approve') && !this->auth->acl_get('m_kb_disapprove'))
					{
						redirect($this->helper->url('kb/index'));
					}
					else
					{
						redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
					}
				}
				else
				{
					confirm_box(false, $this->user->lang['DENY_CONFIRM']);

					redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
				}
			break;

			case 'disapprove':
				if (!$this->auth->acl_get('m_kb_disapprove'))
				{
					trigger_error('NOT_AUTHORISED');
				}

				if (!$row['article_approved'] && !$row['article_denied'])
				{
					trigger_error('CANNOT_DISAPPROVE');
				}

				if (confirm_box(true))
				{
					$sql_array = array(
						'article_approved'	=> 0,
						'article_denied'	=> 0,
					);

					$sql = 'UPDATE ' . $this->kb_articles_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE article_id = ' . (int) $article_id;
					$this->db->sql_query($sql);

					if (!this->auth->acl_get('m_kb_approve') && !this->auth->acl_get('m_kb_deny'))
					{
						redirect($this->helper->url('kb/index'));
					}
					else
					{
						redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
					}
				}
				else
				{
					confirm_box(false, $this->user->lang['DISAPPROVE_CONFIRM']);

					redirect($this->helper->url('kb/viewarticle', 'a=' . $row['article_id']));
				}
			break;
		}

		page_header();

		page_footer();
	}
/*
	public function posting()
	{
		$this->user->add_lang('posting');

		$mode = $this->request->variable('mode', '');

		if ($mode != 'delete' && $mode != 'edit' && $mode != 'post')
		{
			trigger_error('NO_POST_MODE');
		}

		$this->template->assign_vars(array(
			'VAR' => 'This is a var with this mode: ' . $mode,
		));

		page_header($this->user->lang['KNOWLEDGE_BASE'] . ' - posting');

		return $this->helper->render('kb_posting.html');
	}
*/
	public function viewarticle()
	{
		$article_id = $this->request->variable('a', 0);

		if (!$article_id)
		{
			trigger_error('NO_ARTICLE');
		}

		$sql_where = (!$this->auth->acl_get('m_kb_approve') && !$this->auth->acl_get('m_kb_deny') && !$this->auth->acl_get('m_kb_disapprove')) ? ' AND article_approved = 1' : '';

		$sql = 'SELECT a.*, c.*, u.user_id, u.user_colour, u.username
			FROM ' . $this->kb_articles_table . ' a, ' . $this->kb_categories_table . ' c, ' . USERS_TABLE . ' u
			WHERE u.user_id = a.article_poster
				AND a.article_id = ' . (int) $article_id . "
				AND a.category_id = c.category_id
				$sql_where";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('NO_ARTICLE');
		}

		$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
			(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
			(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
		$text = generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

		$this->template->assign_vars(array(
			'ARTICLE_TITLE'			=> $row['article_title'],
			'ARTICLE_DESCRIPTION'	=> $row['article_description'],
			'ARTICLE_POSTER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'ARTICLE_CATEGORY'		=> $row['category_name'],
			'ARTICLE_TIME'			=> $this->user->format_date($row['article_time']),
			'MESSAGE'				=> $text,

			'S_DENIED'	=> $row['article_denied'] ? true : false,

			'U_APPROVE'	=> $this->auth->acl_get('m_kb_approve') ? ($row['article_approved'] ? '' : $this->helper->url('kb/mcp', 'mode=approve&amp;a=' . $row['article_id'])) : '',
			'U_DELETE'	=> $this->auth->acl_get('m_kb_delete') ? $this->helper->url('kb/mcp', 'mode=delete&amp;a=' . $row['article_id']) : (($this->auth->acl_get('u_kb_delete') && $this->user->data['user_id'] = $row['article_poster']) ? $this->helper->url('kb/posting', 'mode=delete&amp;a=' . $row['article_id']) : ''),
			'U_DENY'	=> $this->auth->acl_get('m_kb_deny') ? ($row['article_denied'] ? '' : $this->helper->url('kb/mcp', 'mode=deny&amp;a=' . $row['article_id'])) : '',
			'U_DISAPPROVE'	=> $this->auth->acl_get('m_kb_disapprove') ? ($row['article_approved'] || $row['article_denied'] ? $this->helper->url('kb/mcp', 'mode=disapprove&amp;a=' . $row['article_id']) : '') : '',
			'U_EDIT'	=> ($this->auth->acl_get('m_kb_edit') || ($this->auth->acl_get('u_kb_edit') && $user->data['user_id'] = $row['article_poster'])) ? $this->helper->url('kb/posting', 'mode=edit&amp;a=' . $row['article_id']) : '',
		));

		page_header($this->user->lang['KNOWLEDGE_BASE'] . ' - ' . $row['article_title']);

		return $this->helper->render('kb_viewarticle_body.html');

		page_footer();
	}
}
