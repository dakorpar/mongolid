<?php
namespace Mongolid\DataMapper;

use Mockery as m;
use MongoDB\BSON\ObjectID;
use Mongolid\Model\HasAttributesInterface;
use Mongolid\Model\HasAttributesTrait;
use Mongolid\Model\PolymorphableInterface;
use Mongolid\Schema\AbstractSchema;
use Mongolid\TestCase;
use stdClass;

class EntityAssemblerTest extends TestCase
{
    /**
     * @dataProvider entityAssemblerFixture
     */
    public function testShouldAssembleEntityForTheGivenSchema($inputValue, $availableSchemas, $inputSchema, $expectedOutput)
    {
        // Arrange
        $entityAssembler = new EntityAssembler();
        $schemas = [];
        foreach ($availableSchemas as $key => $value) {
            $schemas[$key] = $this->instance($key, m::mock(AbstractSchema::class.'[]'));
            $schemas[$key]->entityClass = $value['entityClass'];
            $schemas[$key]->fields = $value['fields'];
        }

        // Act
        $result = $entityAssembler->assemble($inputValue, $schemas[$inputSchema]);

        // Assert
        $this->assertEquals($expectedOutput, $result);
    }

    public function entityAssemblerFixture()
    {
        return [
            //---------------------------

            'A simple schema to a entity' => [
                'inputValue' => [ // Data that will be used to assembly the entity
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'grade' => 7.25,
                ],
                'availableSchmas' => [ // Schemas that will exist in the test context
                    'studentSchema' => [
                        'entityClass' => StubStudent::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'name' => 'string',
                            'age' => 'integer',
                            'grade' => 'float',
                            'finalGrade' => 'float',
                        ],
                    ],
                ],
                'inputSchema' => 'studentSchema', // Schema that will be used to assembly $inputValue
                'expectedOutput' => new StubStudent([ // Expected output
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'grade' => 7.25,
                ]),
            ],

            //---------------------------

            'A schema containing an embeded schema but with null field' => [
                'inputValue' => [ // Data that will be used to assembly the entity
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => null,
                    'finalGrade' => 7.25,
                ],
                'availableSchmas' => [ // Schemas that will exist in the test context
                    'studentSchema' => [
                        'entityClass' => StubStudent::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'name' => 'string',
                            'age' => 'integer',
                            'tests' => 'schema.TestSchema',
                            'finalGrade' => 'float',
                        ],
                    ],
                    'TestSchema' => [
                        'entityClass' => StubTestGrade::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'subject' => 'string',
                            'grade' => 'float',
                        ],
                    ],
                ],
                'inputSchema' => 'studentSchema', // Schema that will be used to assembly $inputValue
                'expectedOutput' => new StubStudent([ // Expected output
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => null,
                    'finalGrade' => 7.25,
                ]),
            ],

            //---------------------------

            'A stdClass with a schema containing an embeded schema with a document directly into the field' => [
                'inputValue' => (object) [ // Data that will be used to assembly the entity
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => [
                        '_id' => new ObjectID('507f1f77bcf86cd7994390ea'),
                        'subject' => 'math',
                        'grade' => 7.25,
                    ],
                    'finalGrade' => 7.25,
                ],
                'availableSchmas' => [ // Schemas that will exist in the test context
                    'studentSchema' => [
                        'entityClass' => StubStudent::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'name' => 'string',
                            'age' => 'integer',
                            'tests' => 'schema.TestSchema',
                            'finalGrade' => 'float',
                        ],
                    ],
                    'TestSchema' => [
                        'entityClass' => StubTestGrade::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'subject' => 'string',
                            'grade' => 'float',
                        ],
                    ],
                ],
                'inputSchema' => 'studentSchema', // Schema that will be used to assembly $inputValue
                'expectedOutput' => new StubStudent([ // Expected output
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => [
                        new StubTestGrade([
                            '_id' => new ObjectID('507f1f77bcf86cd7994390ea'),
                            'subject' => 'math',
                            'grade' => 7.25,
                        ]),
                    ],
                    'finalGrade' => 7.25,
                ]),
            ],

            //---------------------------

            'A schema containing an embeded schema with multiple documents in the field' => [
                'inputValue' => [ // Data that will be used to assembly the entity
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => [
                        [
                            '_id' => new ObjectID('507f1f77bcf86cd7994390ea'),
                            'subject' => 'math',
                            'grade' => 7.25,
                        ],
                        [
                            '_id' => new ObjectID('507f1f77bcf86cd7994390eb'),
                            'subject' => 'english',
                            'grade' => 9.0,
                        ],
                    ],
                    'finalGrade' => 7.25,
                ],
                'availableSchmas' => [ // Schemas that will exist in the test context
                    'studentSchema' => [
                        'entityClass' => StubStudent::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'name' => 'string',
                            'age' => 'integer',
                            'tests' => 'schema.TestSchema',
                            'finalGrade' => 'float',
                        ],
                    ],
                    'TestSchema' => [
                        'entityClass' => StubTestGrade::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'subject' => 'string',
                            'grade' => 'float',
                        ],
                    ],
                ],
                'inputSchema' => 'studentSchema', // Schema that will be used to assembly $inputValue
                'expectedOutput' => new StubStudent([ // Expected output
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'tests' => [
                        new StubTestGrade([
                            '_id' => new ObjectID('507f1f77bcf86cd7994390ea'),
                            'subject' => 'math',
                            'grade' => 7.25,
                        ]),
                        new StubTestGrade([
                            '_id' => new ObjectID('507f1f77bcf86cd7994390eb'),
                            'subject' => 'english',
                            'grade' => 9.0,
                        ]),
                    ],
                    'finalGrade' => 7.25,
                ]),
            ],

            //---------------------------

            'A simple schema with a polymorphable interface' => [
                'inputValue' => [ // Data that will be used to assembly the entity
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'grade' => 7.25,
                ],
                'availableSchmas' => [ // Schemas that will exist in the test context
                    'studentSchema' => [
                        'entityClass' => PolymorphableStudent::class,
                        'fields' => [
                            '_id' => 'objectId',
                            'name' => 'string',
                            'age' => 'integer',
                            'grade' => 'float',
                            'finalGrade' => 'float',
                        ],
                    ],
                ],
                'inputSchema' => 'studentSchema', // Schema that will be used to assembly $inputValue
                'expectedOutput' => new StubStudent([ // Expected output
                    '_id' => new ObjectID('507f1f77bcf86cd799439011'),
                    'name' => 'John Doe',
                    'age' => 25,
                    'grade' => 7.25,
                ]),
            ],
        ];
    }
}

class StubStudent extends stdClass implements HasAttributesInterface
{
    use HasAttributesTrait;

    public function __construct($attr = [])
    {
        foreach ($attr as $key => $value) {
            $this->$key = $value;
        }

        $this->syncOriginalDocumentAttributes();
    }
}

class StubTestGrade extends stdClass
{
    public function __construct($attr = [])
    {
        foreach ($attr as $key => $value) {
            $this->$key = $value;
        }
    }
}

class PolymorphableStudent extends stdClass implements PolymorphableInterface
{
    public function __construct($attr = [])
    {
        foreach ($attr as $key => $value) {
            $this->$key = $value;
        }
    }

    public function polymorph()
    {
        return new StubStudent((array) $this);
    }
}
