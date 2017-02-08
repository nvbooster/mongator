<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;

class CorePolymorphicReferencesTest extends TestCase
{
    public function testDocumentReferencesOneSetterGetter()
    {
        $article = $this->mongator->create('Model\Article');

        $author = $this->mongator->create('Model\Author')->setName('foo')->save();
        $category = $this->mongator->create('Model\Category')->setName('foo')->save();

        $this->assertSame($article, $article->setLike($author));
        $this->assertSame($author, $article->getLike());
        $this->assertSame(array(
            '_MongatorDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ), $article->getLikeRef());
        $article->setLike($category);
        $this->assertSame(array(
            '_MongatorDocumentClass' => 'Model\Category',
            'id' => $category->getId(),
        ), $article->getLikeRef());
        $this->assertSame($category, $article->getLike());
    }

    public function testDocumentReferencesOneSetterGetterDiscriminatorMap()
    {
        $article = $this->mongator->create('Model\Article');

        $author = $this->mongator->create('Model\Author')->setName('foo')->save();
        $category = $this->mongator->create('Model\Category')->setName('foo')->save();

        $this->assertSame($article, $article->setFriend($author));
        $this->assertSame($author, $article->getFriend());
        $this->assertSame(array(
            'name' => 'au',
            'id' => $author->getId(),
        ), $article->getFriendRef());
        $article->setFriend($category);
        $this->assertSame(array(
            'name' => 'ct',
            'id' => $category->getId(),
        ), $article->getFriendRef());
        $this->assertSame($category, $article->getFriend());
    }

    public function testDocumentReferencesOneGetterQuery()
    {
        $author = $this->mongator->create('Model\Author')->setName('foo')->save();

        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getLike());
        $article->setLikeRef(array(
            '_MongatorDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ));
        $this->assertSame($author, $article->getLike());
    }

    public function testDocumentReferencesOneGetterQueryDiscriminatorMap()
    {
        $category = $this->mongator->create('Model\Category')->setName('foo')->save();

        $article = $this->mongator->create('Model\Article');
        $this->assertNull($article->getLike());
        $article->setFriendRef(array(
            'name' => 'ct',
            'id' => $category->getId(),
        ));
        $this->assertSame($category, $article->getFriend());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesOneSetterInvalidClass()
    {
        $this->mongator->create('Model\Article')->setLike(new \DateTime());
    }

    public function testDocumentUpdateReferenceFieldsReferencesOne()
    {
        $author = $this->mongator->create('Model\Author');
        $article = $this->mongator->create('Model\Article')->setLike($author);
        $author->setId(new ObjectID());
        $article->updateReferenceFields();
        $this->assertSame(array(
            '_MongatorDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ), $article->getLikeRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesOneDiscriminatorMap()
    {
        $author = $this->mongator->create('Model\Author');
        $article = $this->mongator->create('Model\Article')->setFriend($author);
        $author->setId(new ObjectID());
        $article->updateReferenceFields();
        $this->assertSame(array(
            'name' => 'au',
            'id' => $author->getId(),
        ), $article->getFriendRef());
    }

    public function testDocumentQueryForSaveReferencesOne()
    {
        $author = $this->mongator->create('Model\Author')->setName('pablodip')->save();
        $article = $this->mongator->create('Model\Article')->setLike($author);

        $this->assertSame(array(
            'like' => array(
                '_MongatorDocumentClass' => 'Model\Author',
                'id' => $author->getId(),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentReferencesManyGetter()
    {
        $article = $this->mongator->create('Model\Article');
        $related = $article->getRelated();
        $this->assertInstanceOf('Mongator\Group\PolymorphicReferenceGroup', $related);
        $this->assertSame('_MongatorDocumentClass', $related->getDiscriminatorField());
        $this->assertSame($article, $related->getParent());
        $this->assertSame('relatedRef', $related->getField());
        $this->assertSame($related, $article->getRelated());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNew()
    {
        $article = $this->mongator->create('Model\Article');
        $related = $article->getRelated();
        $author1 = $this->mongator->create('Model\Author')->setId(new ObjectID());
        $author2 = $this->mongator->create('Model\Author')->setId(new ObjectID());
        $category1 = $this->mongator->create('Model\Category')->setId(new ObjectID());
        $user1 = $this->mongator->create('Model\User')->setId(new ObjectID());
        $related->add(array($author1, $author2, $category1, $user1));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_MongatorDocumentClass' => 'Model\Author', 'id' => $author1->getId()),
            array('_MongatorDocumentClass' => 'Model\Author', 'id' => $author2->getId()),
            array('_MongatorDocumentClass' => 'Model\Category', 'id' => $category1->getId()),
            array('_MongatorDocumentClass' => 'Model\User', 'id' => $user1->getId()),
        ), $article->getRelatedRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNotNew()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new ObjectID(),
            'related' => $relatedRef = array(
                array('_MongatorDocumentClass' => 'Model\Author', 'id' => new ObjectID()),
                array('_MongatorDocumentClass' => 'Model\Author', 'id' => new ObjectID()),
                array('_MongatorDocumentClass' => 'Model\Category', 'id' => new ObjectID()),
                array('_MongatorDocumentClass' => 'Model\Category', 'id' => new ObjectID()),
            ),
        ));
        $related = $article->getRelated();
        $add = array();
        $related->add($add[] = $this->mongator->create('Model\User')->setId(new ObjectID()));
        $related->add($add[] = $this->mongator->create('Model\Author')->setId(new ObjectID()));
        $related->add($add[] = $this->mongator->create('Model\Author')->setId(new ObjectID()));
        $related->remove($this->mongator->create('Model\Author')->setId($relatedRef[1]['id']));
        $related->remove($this->mongator->create('Model\Category')->setId($relatedRef[3]['id']));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_MongatorDocumentClass' => 'Model\Author', 'id' => $relatedRef[0]['id']),
            array('_MongatorDocumentClass' => 'Model\Category', 'id' => $relatedRef[2]['id']),
            array('_MongatorDocumentClass' => get_class($add[0]), 'id' => $add[0]->getId()),
            array('_MongatorDocumentClass' => get_class($add[1]), 'id' => $add[1]->getId()),
            array('_MongatorDocumentClass' => get_class($add[2]), 'id' => $add[2]->getId()),
        ), $article->getRelatedRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNewDiscriminatorMap()
    {
        $article = $this->mongator->create('Model\Article');
        $elements = $article->getElements();
        $element1 = $this->mongator->create('Model\FormElement')->setId(new ObjectID());
        $element2 = $this->mongator->create('Model\FormElement')->setId(new ObjectID());
        $textareaElement1 = $this->mongator->create('Model\TextareaFormElement')->setId(new ObjectID());
        $radioElement1 = $this->mongator->create('Model\RadioFormElement')->setId(new ObjectID());
        $elements->add(array($element1, $element2, $textareaElement1, $radioElement1));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('type' => 'element', 'id' => $element1->getId()),
            array('type' => 'element', 'id' => $element2->getId()),
            array('type' => 'textarea', 'id' => $textareaElement1->getId()),
            array('type' => 'radio', 'id' => $radioElement1->getId()),
        ), $article->getElementsRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNotNewDiscriminatorMap()
    {
        $article = $this->mongator->create('Model\Article')->setDocumentData(array(
            '_id' => new ObjectID(),
            'elements' => $elementsRef = array(
                array('type' => 'element', 'id' => new ObjectID()),
                array('type' => 'element', 'id' => new ObjectID()),
                array('type' => 'textarea', 'id' => new ObjectID()),
                array('type' => 'textarea', 'id' => new ObjectID()),
            ),
        ));
        $elements = $article->getElements();
        $add = array();
        $elements->add($add[] = $this->mongator->create('Model\RadioFormElement')->setId(new ObjectID()));
        $elements->add($add[] = $this->mongator->create('Model\FormElement')->setId(new ObjectID()));
        $elements->add($add[] = $this->mongator->create('Model\FormElement')->setId(new ObjectID()));
        $elements->remove($this->mongator->create('Model\FormElement')->setId($elementsRef[1]['id']));
        $elements->remove($this->mongator->create('Model\TextareaFormElement')->setId($elementsRef[3]['id']));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('type' => 'element', 'id' => $elementsRef[0]['id']),
            array('type' => 'textarea', 'id' => $elementsRef[2]['id']),
            array('type' => 'radio', 'id' => $add[0]->getId()),
            array('type' => 'element', 'id' => $add[1]->getId()),
            array('type' => 'element', 'id' => $add[2]->getId()),
        ), $article->getElementsRef());
    }

    /*
     * Related to Mongator\Group\PolymorphicReferenceMany
     */
    public function testReferencesManyQuery()
    {
        $authors = array();
        for ($i = 0; $i < 9; $i++) {
            $authors[] = $this->mongator->create('Model\Author')->setName('Author'.$i)->save();
        }
        $categories = array();
        for ($i = 0; $i < 9; $i++) {
            $categories[] = $this->mongator->create('Model\Category')->setName('Category'.$i)->save();
        }
        $users = array();
        for ($i = 0; $i < 9; $i++) {
            $users[] = $this->mongator->create('Model\User')->setUsername('User'.$i)->save();
        }

        $relatedRef = array();
        $relatedRef[] = array('_MongatorDocumentClass' => 'Model\Author', 'id' => $authors[3]->getId());
        $relatedRef[] = array('_MongatorDocumentClass' => 'Model\Author', 'id' => $authors[5]->getId());
        $relatedRef[] = array('_MongatorDocumentClass' => 'Model\Category', 'id' => $categories[1]->getId());
        $relatedRef[] = array('_MongatorDocumentClass' => 'Model\User', 'id' => $users[8]->getId());
        $article = $this->mongator->create('Model\Article')->setRelatedRef($relatedRef);
        $this->assertSame(array(
            $authors[3],
            $authors[5],
            $categories[1],
            $users[8],
        ), $article->getRelated()->getSaved());
    }

    public function testReferencesManyQueryDiscriminatorMap()
    {
        $elements = array();
        for ($i = 0; $i < 9; $i++) {
            $elements[] = $this->mongator->create('Model\FormElement')->setLabel('Element'.$i)->save();
        }
        $textareaElements = array();
        for ($i = 0; $i < 9; $i++) {
            $textareaElements[] = $this->mongator->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }
        $radioElements = array();
        for ($i = 0; $i < 9; $i++) {
            $radioElements[] = $this->mongator->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $elementsRef = array();
        $elementsRef[] = array('type' => 'element', 'id' => $elements[3]->getId());
        $elementsRef[] = array('type' => 'element', 'id' => $elements[5]->getId());
        $elementsRef[] = array('type' => 'textarea', 'id' => $textareaElements[1]->getId());
        $elementsRef[] = array('type' => 'radio', 'id' => $radioElements[8]->getId());
        $article = $this->mongator->create('Model\Article')->setElementsRef($elementsRef);
        $this->assertSame(array(
            $elements[3],
            $elements[5],
            $textareaElements[1],
            $radioElements[8],
        ), $article->getElements()->getSaved());
    }

    public function testDocumentQueryForSaveReferencesMany()
    {
        $article = $this->mongator->create('Model\Article');
        $related = $article->getRelated();
        $author = $this->mongator->create('Model\Author')->setName('foo')->save();
        $category = $this->mongator->create('Model\Category')->setName('bar')->save();
        $related->add(array($author, $category));
        $article->updateReferenceFields();

        $this->assertSame(array(
            'related' => array(
                array(
                    '_MongatorDocumentClass' => 'Model\Author',
                    'id' => $author->getId(),
                ),
                array(
                    '_MongatorDocumentClass' => 'Model\Category',
                    'id' => $category->getId(),
                ),
            ),
        ), $article->queryForSave());
    }

    public function testBasicQueryForSave()
    {
        $element = $this->mongator->create('Model\FormElement')->setLabel('foo');
        $this->assertSame(array(
            'label' => 'foo',
            'type'  => 'formelement',
        ), $element->queryForSave());
        $element->save();
        $element->setLabel('bar');
        $this->assertSame(array(
            '$set' => array(
                'label' => 'bar',
            ),
        ), $element->queryForSave());

        $textareaElement = $this->mongator->create('Model\TextareaFormElement')->setLabel('ups');
        $this->assertSame(array(
            'label' => 'ups',
            'type' => 'textarea',
        ), $textareaElement->queryForSave());
        $textareaElement->save();
        $textareaElement->setLabel('zam');
        $this->assertSame(array(
            '$set' => array(
                'label' => 'zam',
            ),
        ), $textareaElement->queryForSave());
    }
}
