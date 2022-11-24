<?php

namespace Magenerds\PageDesigner\Job;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockSearchResultsInterface;
use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Migration
{
    /**
     * Construct.
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param BlockRepositoryInterface $blockRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepositoryInterface,
        BlockRepositoryInterface $blockRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get a list of all CMS pages.
     *
     * @return PageSearchResultsInterface
     */
    public function getPages() {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $pages = $this->pageRepositoryInterface->getList($searchCriteria)->getItems();
        return $pages;
    }

    /**
     * Get a list of all CMS Blocks.
     *
     * @return BlockSearchResultsInterface
     */
    public function getBlocks() {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $blocks = $this->blockRepositoryInterface->getList($searchCriteria)->getItems();
        return $blocks;
    }

    /**
     * Migrate all Pages and Blocks to PageDesigner.
     */
    public function migrate()
    {
        foreach ($this->getPages() as $page) {
            $this->migrateContent($page);
        }

        foreach ($this->getBlocks() as $block) {
            $this->migrateContent($block);
        }
    }

    /**
     * Revert all Pages and Blocks back into default format.
     */
    public function revert()
    {
        foreach ($this->getPages() as $page) {
            $this->revertContent($page);
        }

        foreach ($this->getBlocks() as $block) {
            $this->revertContent($block);
        }
    }

    /**
     * Migrate Page/Block content to a PageDesigner structure.
     *
     * @param Page|Block $entity
     */
    public function migrateContent($entity)
    {
        if ($entity->getPageDesignerJson() !== null) {
            return;
        }
        $content = $entity->getContent();
        $widget = '{{widget type="Magenerds\WysiwygWidget\Block\Widget\Editor" content="---BASE64---' . base64_encode($content) . '"}}';
        $pageDesignerJson = [
            'version' => '1.0.0',
            'rows' => [
                [
                    'columns' => [
                        [
                            'gridSize' => [
                                'md' => 12
                            ],
                            'content' => $widget,
                            'settings' => []
                        ]
                    ],
                    'settings' => []
                ]
            ]
        ];
        $entity->setPageDesignerJson(json_encode($pageDesignerJson));
        $entity->setContent('<div class="pd-row row"><div class="pd-col col-md-12">' . $widget . '</div></div>');
        $entity->getResource()->save($entity);
    }

    /**
     * Get first wysiwyg widget and revert it's content into content.
     *
     * @param Page|Block $entity
     */
    public function revertContent($entity) {
        if ($entity->getPageDesignerJson() === null) {
            return;
        }

        $pageDesignerJson = json_decode($entity->getPageDesignerJson(), true);

        foreach ($pageDesignerJson['rows'] as $row) {
            foreach ($row['columns'] as $column) {
                if (strpos($column['content'], 'Magenerds\WysiwygWidget\Block\Widget\Editor') === false) {
                    continue;
                }
                $content = base64_decode(str_replace('"}}', '', str_replace('{{widget type="Magenerds\WysiwygWidget\Block\Widget\Editor" content="---BASE64---', '', $column['content'])));
                $entity->setPageDesignerJson(null);
                $entity->setContent($content);
                $entity->getResource()->save($entity);
                return;
            }
        }
    }
}
