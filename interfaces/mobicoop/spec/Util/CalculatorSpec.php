<?php 

namespace App\Spec\Util;
use App\Util\Calculator;

/* This is a sample Unit Test  */
describe('CalculatorUtil', function () {
    describe('randAndSquare', function () {
        it('randAndSquare should return à squared between two number', function () {
            $calcul = new Calculator();
            $nb = $calcul->randAndSquare(4,8);

            expect($nb)->toBeGreaterThan(15);
            expect($nb)->toBeLessThan(65);
        });
    });
});

?>