<?php

use App\Domain\Risk\Normalization\NormalizeSport;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

dataset('football aliases', [
    'Football',
    'football',
    'SOCCER',
    'Association Football',
    ' football ',
    "football\n",
]);

test('recognized football aliases normalize to the football code', function (string $input) {
    $result = (new NormalizeSport)->normalize($input);

    expect($result->sportCode)->toBe('football');
    expect($result->status)->toBe(NormalizationStatus::Complete);
    expect($result->rawInput)->toBe($input);
})->with('football aliases');

test('known but unsupported sports are marked unsupported, not unrecognized', function (string $input) {
    $result = (new NormalizeSport)->normalize($input);

    expect($result->sportCode)->toBeNull();
    expect($result->status)->toBe(NormalizationStatus::Unsupported);
})->with([
    'Tennis',
    'Basketball',
    'Cricket',
    'Baseball',
    'Horse Racing',
    'Esports',
    'Virtual Sports',
]);

test('gibberish input is unrecognized, not unsupported', function () {
    $result = (new NormalizeSport)->normalize('asdkjhasd');

    expect($result->sportCode)->toBeNull();
    expect($result->status)->toBe(NormalizationStatus::Unrecognized);
});

test('the same input always produces the same result', function () {
    $normalizer = new NormalizeSport;

    $first = $normalizer->normalize('Football');
    $second = $normalizer->normalize('Football');

    expect($first->sportCode)->toBe($second->sportCode);
    expect($first->status)->toBe($second->status);
});
