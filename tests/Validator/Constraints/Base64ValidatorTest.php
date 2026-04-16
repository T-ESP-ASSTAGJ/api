<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\Base64;
use App\Validator\Constraints\Base64Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class Base64ValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): Base64Validator
    {
        return new Base64Validator();
    }

    public function testThrowsOnWrongConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $wrongConstraint = $this->createMock(Constraint::class);
        $this->validator->validate('someValue', $wrongConstraint);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Base64());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Base64());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider provideNonStringValues
     */
    public function testThrowsOnNonStringValue(mixed $value): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate($value, new Base64());
    }

    public static function provideNonStringValues(): iterable
    {
        yield 'integer' => [42];
        yield 'float' => [3.14];
        yield 'array' => [['foo']];
        yield 'object' => [new \stdClass()];
        yield 'bool' => [true];
    }

    /**
     * @dataProvider provideValidBase64Values
     */
    public function testValidBase64RaisesNoViolation(string $value): void
    {
        $this->validator->validate($value, new Base64());

        $this->assertNoViolation();
    }

    public static function provideValidBase64Values(): iterable
    {
        yield 'simple base64' => [base64_encode('Hello, World!')];
        yield 'base64 with padding =' => [base64_encode('foo')];
        yield 'base64 with padding ==' => [base64_encode('fo')];
        yield 'base64 with no padding' => [base64_encode('foob')];
        yield 'base64 encoded binary-like data' => [base64_encode(random_bytes(32))];
        yield 'data URI image/png prefix' => ['data:image/png;base64,'.base64_encode('fakeimage')];
        yield 'data URI image/jpeg prefix' => ['data:image/jpeg;base64,'.base64_encode('fakejpeg')];
        yield 'data URI application/pdf prefix' => ['data:application/pdf;base64,'.base64_encode('fakepdf')];
        yield 'empty base64 (zero bytes)' => [''];
    }

    /**
     * @dataProvider provideInvalidBase64Values
     */
    public function testInvalidBase64RaisesViolation(string $value): void
    {
        $constraint = new Base64();

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setCode('INVALID_BASE64_ERROR')
            ->assertRaised();
    }

    /**
     * Expose the protected ConstraintValidator::formatValue() so tests can mirror
     * exactly what the validator passes to setParameter().
     */
    private function formatValue(mixed $value): string
    {
        return (new class extends Base64Validator {
            public function expose(mixed $v): string
            {
                return $this->formatValue($v);
            }
        })->expose($value);
    }

    public static function provideInvalidBase64Values(): iterable
    {
        yield 'contains spaces' => ['SGVsbG8g V29ybGQ='];
        yield 'contains special chars' => ['SGVsbG8!V29ybGQ='];
        yield 'contains newlines' => ["SGVsbG8\nV29ybGQ="];
        yield 'too many padding chars' => ['SGVsbG8==='];
        yield 'invalid characters only' => ['!!!'];
    }

    public function testValidCharsetButStrictDecodeFailureRaisesViolation(): void
    {
        $value = 'A';
        $constraint = new Base64();

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setCode('INVALID_BASE64_ERROR')
            ->assertRaised();
    }
}
