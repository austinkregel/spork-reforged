<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Jobs\OperationJob;
use App\Services\Facades\Operator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Pennant\Feature;
use Tests\Integration\Operations\AnotherOperation;
use Tests\Integration\Operations\CustomConnectionOperation;
use Tests\Integration\Operations\CustomQueueOperation;
use Tests\Integration\Operations\ExampleOperation;
use Tests\TestCase;

class OperatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2019-07-04 12:00:00');
        Queue::fake();
        config([
            'operations.operations' => [
                ExampleOperation::class,
                AnotherOperation::class,
                CustomQueueOperation::class,
                CustomConnectionOperation::class,
            ],
        ]);
    }

    /** @test */
    public function it_queues_operations_that_are_ready_to_run(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->subMinutes(5);
        $exampleOperation->save();

        Operator::queue();

        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($exampleOperation) {
            return $job->getOperation()->is($exampleOperation);
        });

        $this->assertNotNull($exampleOperation->fresh()->started_run_at);
    }

    /** @test */
    public function it_queues_multiple_operations_that_are_ready_to_run(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->subMinutes(5);
        $exampleOperation->save();

        $firstAnotherOperation = new AnotherOperation();
        $firstAnotherOperation->should_run_at = Carbon::now()->subMinutes(9);
        $firstAnotherOperation->save();

        $secondAnotherOperation = new AnotherOperation();
        $secondAnotherOperation->should_run_at = Carbon::now();
        $secondAnotherOperation->save();

        Operator::queue();

        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($exampleOperation) {
            return $job->getOperation()->is($exampleOperation);
        });
        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($firstAnotherOperation) {
            return $job->getOperation()->is($firstAnotherOperation);
        });
        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($secondAnotherOperation) {
            return $job->getOperation()->is($secondAnotherOperation);
        });

        $this->assertNotNull($exampleOperation->fresh()->started_run_at);
        $this->assertNotNull($firstAnotherOperation->fresh()->started_run_at);
        $this->assertNotNull($secondAnotherOperation->fresh()->started_run_at);
    }

    /** @test */
    public function it_runs_operations_with_a_custom_queue_on_the_custom_queue_when_scheduled_and_queued(): void
    {
        $customQueueOperation = CustomQueueOperation::schedule(Carbon::now()->subMinutes(5));

        Operator::queue();

        Queue::assertPushedOn('custom', OperationJob::class);
        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($customQueueOperation) {
            return $job->getOperation()->is($customQueueOperation);
        });
    }

    /** @test */
    public function it_runs_operations_with_a_custom_connection_on_the_custom_connection_when_scheduled_and_queued(): void
    {
        $customConnectionOperation = CustomConnectionOperation::schedule(Carbon::now()->subMinutes(5));

        Operator::queue();

        Queue::assertPushed(OperationJob::class, function (OperationJob $job) use ($customConnectionOperation) {
            return $job->connection === 'custom'
                && $job->getOperation()->is($customConnectionOperation);
        });
    }

    /** @test */
    public function it_does_not_queue_operations_that_are_not_ready_to_run(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->addMinutes(5);
        $exampleOperation->save();

        Operator::queue();

        Queue::assertNothingPushed();

        $this->assertNull($exampleOperation->fresh()->started_run_at);
    }

    /** @test */
    public function it_does_not_queue_operations_that_have_already_started_to_run(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->subMinutes(5);
        $exampleOperation->started_run_at = Carbon::now()->subMinute();
        $exampleOperation->save();

        Operator::queue();

        Queue::assertNothingPushed();

        $this->assertEquals(Carbon::now()->subMinute(), $exampleOperation->fresh()->started_run_at);
    }

    /** @test */
    public function it_does_not_queue_operations_that_have_been_deleted(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->subMinutes(5);
        $exampleOperation->deleted_at = Carbon::now()->subMinute();
        $exampleOperation->save();

        Operator::queue();

        Queue::assertNothingPushed();

        $this->assertNull($exampleOperation->fresh()->started_run_at);
    }

    /** @test */
    public function it_calls_an_operations_queue_event_when_event_is_queued(): void
    {
        $exampleOperation = new ExampleOperation();
        $exampleOperation->should_run_at = Carbon::now()->subMinutes(5);
        $exampleOperation->save();

        $pendingOperation = new ExampleOperation();
        $pendingOperation->should_run_at = Carbon::now()->addMinutes(5);
        $pendingOperation->save();

        Operator::queue();

        $this->assertTrue($exampleOperation->fresh()->hookedIntoQueue());
        $this->assertFalse($pendingOperation->fresh()->hookedIntoQueue());
    }
}
