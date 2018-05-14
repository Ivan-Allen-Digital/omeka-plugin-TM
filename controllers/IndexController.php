<?php
/**
 * TM
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The TM index controller class.
 *
 * @package TM
 */
class TM_IndexController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Tag');
    }

    public function addAction()
    {
        $this->_helper->redirector('');
    }

    public function editAction()
    {
        $this->_helper->redirector('');
    }

    /**
     *
     * @return void
     */
    public function browseAction()
    {
    }

    public function gettagsAction()
    {
        $params = $this->_getAllParams();

        //Check to see whether it will be tags for exhibits or for items
        //Default is Item
        if (isset($params['tagType'])) {
            $for = $params['tagType'];
            unset($params['tagType']);
        } else {
            $for = 'Item';
        }
        //Since tagType must correspond to a valid classname, this will barf an error on Injection attempts
        if (!class_exists($for)) {
            throw new InvalidArgumentException(__('Invalid tagType given.'));
        }

        if ($record = $this->_getParam('record')) {
            $filter['record'] = $record;
        }

        $findByParams = array_merge(array('sort_field' => 'name', 'include_zero' => true),
            $params,
            array('type' => $for));

        $total_tags = $this->_helper->db->count($findByParams);
        $limit = isset($params['limit']) ? $params['limit'] : 100;
        $page = isset($params['page']) ? $params['page'] : 0;
        $tags = $this->_helper->db->findBy($findByParams, $limit, $page);
        echo json_encode($tags);
    }

    public function autocompleteAction()
    {
        $tagText = $this->_getParam('term');
        if (empty($tagText)) {
            $this->_helper->json(array());
        }
        $tagNames = $this->_helper->db->getTable()->findTagNamesLike($tagText);
        $this->_helper->json($tagNames);
    }

    public function renameAjaxAction()
    {
        $csrf = new Omeka_Form_SessionCsrf;
        $oldTagId = $_POST['id'];
        $oldTag = $this->_helper->db->findById($oldTagId);
        $oldName = $oldTag->name;
        $newName = trim($_POST['value']);

        $oldTag->name = $newName;
        $this->_helper->viewRenderer->setNoRender();
        if ($csrf->isValid($_POST) && $oldTag->save(false)) {
            $this->getResponse()->setBody($newName);
        } else {
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody($oldName);
        }
    }

    public function renametagAction() {
        $params = $this->_getAllParams();

        if (isset($params['id']) && isset($params['replacementTag'])) {
            $currentTagID = $params['id'];
            $replacementTagName = $params['replacementTag'];
            $currentTag = $this->_helper->db->findById($currentTagID);
            $currentTag->rename([$replacementTagName]);
            $this->getResponse()->setHttpResponseCode(200);
            $this->getResponse()->setBody("Tag successfully renamed.");
        } else {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody('Request Malformed.');
        }
    }

    public function deletetagAction()
    {
        $id = $_POST['id'];
        $tag = $this->_helper->db->findById($id);
        $taggings = $tag->getDb()
            ->getTable('RecordsTags')
            ->findBySql('tag_id = ?', array((int) $tag->id));

        foreach ($taggings as $tagging) {
            $tagging->delete();
        }
        $tag->delete();
        $this->getResponse()->setBody("Tag successfully deleted.");
    }

    public function autotagAction()
    {
    }
}
