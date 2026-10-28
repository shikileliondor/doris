<?php

use App\Models\Question;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.organizer_code' => 'pyjama-1234']);
});

test('anyone can open the page to ask a question', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('ask'));
});

test('a question can be sent without an account', function () {
    $this->postJson(route('questions.store'), ['body' => 'Qui ronfle le plus ?'])
        ->assertCreated();

    $this->assertDatabaseHas('questions', ['body' => 'Qui ronfle le plus ?']);
});

test('questions keep nothing about who sent them', function () {
    expect(Schema::getColumnListing('questions'))
        ->toEqualCanonicalizing(['id', 'body', 'created_at', 'updated_at']);
});

test('an empty question is refused', function () {
    $this->postJson(route('questions.store'), ['body' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');

    expect(Question::count())->toBe(0);
});

test('the organizer page asks for the code first', function () {
    Question::factory()->create();

    $this->get(route('organizer.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('organizer/unlock')
            ->missing('questions'),
        );
});

test('a wrong code does not unlock the questions', function () {
    $this->post(route('organizer.unlock'), ['code' => 'pas-le-bon'])
        ->assertSessionHasErrors('code');

    $this->get(route('organizer.show'))
        ->assertInertia(fn (Assert $page) => $page->component('organizer/unlock'));
});

test('nobody can unlock the questions when no code is configured', function () {
    config(['app.organizer_code' => null]);

    $this->post(route('organizer.unlock'), ['code' => ''])
        ->assertSessionHasErrors('code');

    $this->get(route('organizer.show'))
        ->assertInertia(fn (Assert $page) => $page->component('organizer/unlock'));
});

test('the organizer sees every question, newest first', function () {
    $first = Question::factory()->create(['body' => 'Première ?']);
    $second = Question::factory()->create(['body' => 'Deuxième ?']);

    $this->post(route('organizer.unlock'), ['code' => 'pyjama-1234'])
        ->assertRedirect(route('organizer.show'));

    $this->get(route('organizer.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('organizer/questions')
            ->has('questions', 2)
            ->where('questions.0.id', $second->id)
            ->where('questions.0.body', 'Deuxième ?')
            ->where('questions.1.id', $first->id),
        );
});
