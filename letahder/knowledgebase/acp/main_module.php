<?php

namespace letahder\knowledgebase\acp;

class main_module
{
	public $u_action;

	protected $auth;
	protected $db;
	protected $request;
	protected $template;
	protected $user;

	public function main($id, $mode)
	{
		global $auth, $db, $request, $template, $user;
		global $phpbb_admin_path, $phpbb_container, $phpbb_root_path, $phpEx;

		$this->auth = $auth;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->tpl_name = 'acp_kb_categories';

		$kb_categories_table = $phpbb_container->getParameter('tables.kb_categories');
		$kb_articles_table = $phpbb_container->getParameter('tables.kb_articles');

		switch ($mode)
		{
			case 'categories':
				$this->page_title = 'ACP_KNOWLEDGE_BASE_CATEGORIES';

				$action = $this->request->variable('action', '');
				$category_id = $this->request->variable('c', 0);
				$category_name = $this->request->variable('category_name', '', true);
				$delete_articles = $this->request->variable('delete_articles', 0);
				$move_to_category_id = $this->request->variable('move_to_category_id', 0);

				switch ($action)
				{
					case 'add':
						$sql = 'SELECT MAX(right_id) AS right_id
							FROM ' . $kb_categories_table;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$sql_array = array(
							'category_name'	=> $category_name,
							'left_id'		=> $row['right_id'] + 1,
							'right_id'		=> $row['right_id'] + 2,
						);

						$sql = 'INSERT INTO ' . $kb_categories_table . $this->db->sql_build_array('INSERT', $sql_array);
						$this->db->sql_query($sql);

						trigger_error($this->user->lang['ACP_KNOWLEDGE_BASE_CATEGORY_ADDED'] . adm_back_link($this->u_action));
					break;

					case 'edit':
						$sql = 'SELECT category_name
							FROM ' . $kb_categories_table . '
							WHERE category_id = ' . (int) $category_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$submit = ($this->request->is_set_post('submit')) ? true : false;

						if ($submit)
						{
							$sql = 'UPDATE ' . $kb_categories_table . '
								SET category_name = "' . $category_name . '"
								WHERE category_id = ' . (int) $category_id;
							$this->db->sql_query($sql);

							trigger_error($this->user->lang['ACP_KNOWLEDGE_BASE_CATEGORY_EDITED'] . adm_back_link($this->u_action));
						}

						$this->template->assign_vars(array(
							'U_EDIT_CATEGORY'	=> $this->u_action . '&amp;action=edit&amp;c=' . (int) $category_id,
							'CATEGORY_NAME'		=> $row['category_name'],
							'S_CATEGORY_EDIT'	=> true,
						));
					break;

					case 'delete':
						$sql = 'SELECT COUNT(article_id) as articles
							FROM ' . $kb_articles_table . '
							WHERE category_id = ' . (int) $category_id;
						$this->db->sql_query($sql);
						$articles = $this->db->sql_fetchfield('articles');

						if ($articles == 0)
						{
							if (confirm_box(true))
							{
								$sql = 'DELETE FROM ' . $kb_categories_table . '
									WHERE category_id = ' . (int) $category_id;
								$this->db->sql_query($sql);

								trigger_error($this->user->lang['ACP_KNOWLEDGE_BASE_CATEGORY_DELETED'] . adm_back_link($this->u_action));
							}
							else
							{
								confirm_box(false, $this->user->lang['ACP_KNOWLEDGE_BASE_DELETE_CATEGORY_CONFIRM']);
							}
						}
						else
						{
							$submit = ($this->request->is_set_post('submit')) ? true : false;

							if ($submit)
							{
								if ($delete_articles || !$move_to_category_id)
								{
									$sql = 'DELETE FROM ' . $kb_articles_table . '
										WHERE category_id = ' . (int) $category_id;
									$this->db->sql_query($sql);
								}
								else if ($move_to_category_id)
								{
									$sql = 'UPDATE ' . $kb_articles_table . '
										SET category_id = ' . (int) $move_to_category_id . '
										WHERE category_id = ' . (int) $category_id;
									$this->db->sql_query($sql);
								}

								$sql = 'DELETE FROM ' . $kb_categories_table . '
									WHERE category_id = ' . (int) $category_id;
								$this->db->sql_query($sql);

								trigger_error($this->user->lang['ACP_KNOWLEDGE_BASE_CATEGORY_DELETED'] . adm_back_link($this->u_action));
							}
							$this->template->assign_vars(array(
								'U_DELETE_CATEGORY'	=> $this->u_action . '&amp;action=delete&amp;c=' . (int) $category_id,
								'CATEGORY_ID'		=> (int) $category_id,
								'S_CATEGORY_DELETE'	=> true,
							));
						}
					break;

					case 'move_up':
					case 'move_down':
						if (!$category_id)
						{
							trigger_error($this->user->lang['ACP_NO_CATEGORY'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT *
							FROM ' . $kb_categories_table . '
							WHERE category_id = ' . (int) $category_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (!$row)
						{
							trigger_error($this->user->lang['ACP_NO_CATEGORY'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						move_category($row, $action);

						redirect($this->u_action);
					break;
				}

				$sql = 'SELECT *
					FROM ' . $kb_categories_table . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('categories', array(
						'CATEGORY_ID'	=> $row['category_id'],
						'CATEGORY_NAME'	=> $row['category_name'],
						'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;c=' . (int) $row['category_id'],
						'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;c=' . (int) $row['category_id'],
						'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;c=' . (int) $row['category_id'],
						'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;c=' . (int) $row['category_id'],
					));
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'S_CATEGORIES'		=> (sizeof($row) == 0) ? false : true,

					'U_ADD_CATEGORY'	=> $this->u_action . '&amp;action=add',
				));
			break;
		}
	}
}

function move_category($category_row, $action = 'move_up')
{
	global $db, $phpbb_container;

	$kb_categories_table = $phpbb_container->getParameter('tables.kb_categories');

	/**
	* Fetch all the siblings between the module's current spot
	* and where we want to move it to. If there are less than $steps
	* siblings between the current spot and the target then the
	* module will move as far as possible
	*/
	$sql = 'SELECT *
		FROM ' . $kb_categories_table . '
		WHERE ' . (($action == 'move_up') ? "right_id < {$category_row['right_id']} ORDER BY right_id DESC" : "left_id > {$category_row['left_id']} ORDER BY left_id ASC");
	$result = $db->sql_query_limit($sql, 1);

	$target = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$target = $row;
	}
	$db->sql_freeresult($result);

	if (!sizeof($target))
	{
		// The category is already on top or bottom
		return false;
	}

	/**
	* $left_id and $right_id define the scope of the nodes that are affected by the move.
	* $diff_up and $diff_down are the values to substract or add to each node's left_id
	* and right_id in order to move them up or down.
	* $move_up_left and $move_up_right define the scope of the nodes that are moving
	* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
	*/
	if ($action == 'move_up')
	{
		$left_id = $target['left_id'];
		$right_id = $category_row['right_id'];

		$diff_up = $category_row['left_id'] - $target['left_id'];
		$diff_down = $category_row['right_id'] + 1 - $category_row['left_id'];

		$move_up_left = $category_row['left_id'];
		$move_up_right = $category_row['right_id'];
	}
	else
	{
		$left_id = $category_row['left_id'];
		$right_id = $target['right_id'];

		$diff_up = $category_row['right_id'] + 1 - $category_row['left_id'];
		$diff_down = $target['right_id'] - $category_row['right_id'];

		$move_up_left = $category_row['right_id'] + 1;
		$move_up_right = $target['right_id'];
	}

	// Now do the dirty job
	$sql = 'UPDATE ' . $kb_categories_table . "
		SET left_id = left_id + CASE
			WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
			ELSE {$diff_down}
		END,
		right_id = right_id + CASE
			WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
			ELSE {$diff_down}
		END
		WHERE
			left_id BETWEEN {$left_id} AND {$right_id}
			AND right_id BETWEEN {$left_id} AND {$right_id}";
	$db->sql_query($sql);
}
