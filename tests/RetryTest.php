<?php

class RetryTest extends PHPUnit_Framework_TestCase { 

	protected static $exceptionProvider;

	public static function setUpBeforeClass() {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'ExceptionClass.php'; 
		self::$exceptionProvider = new ExceptionClass;
	}

	public function testNoDelayRetry() {
		$cases = [
			[ 
				'method'=> 'runWithException', 'movement' => 1, 
				'correctExceptions' => ['\Exception'], 'tries' => 3, 
				'counter' => 3
			],
			[
				'method'=> 'runWithException', 'movement' => 3, 
				'correctExceptions' => ['\RuntimeException'], 'tries' => 5,
				'counter' => 3
			],
			[
				'method'=> 'runWithRuntimeException', 'movement' => 3, 
				'correctExceptions' => ['\RuntimeException'], 'tries' => 5, 
				'counter' => 15
			],
			[
				'method'=> 'runNoException', 'movement' => 1, 
				'correctExceptions' => ['\RuntimeException'], 'tries' => 3,
				'counter' => 1
			],
		];
		
		foreach ($cases as $caseSettings) {	
			var_dump(
				"Running {$caseSettings['method']} in {$caseSettings['tries']} tries"
				. " and movement {$caseSettings['movement']}.\n"
				. "Expecting counter {$caseSettings['counter']}.\n"
				. "Expecting exceptions: " . implode(',', $caseSettings['correctExceptions'])
			);
			self::$exceptionProvider->reset();
			try {
				\core\utils\Retrying::retry(
					[self::$exceptionProvider, $caseSettings['method']], 
					[$caseSettings['movement']], 
					$caseSettings['correctExceptions'], 
					$caseSettings['tries'], 
					0, 0
				);
			} catch (\Exception $error) {
				var_dump("Got real exception " . get_class($error));
			}
			var_dump("Real counter was: " . self::$exceptionProvider->getCounter());
			$this->assertEquals(
				$caseSettings['counter'], 
				self::$exceptionProvider->getCounter()
			);
		}
	}
	
	public function testDalayRetry() {
		$time = new ExecutionTime;
		$cases = [
			[
				'method'=> 'runWithException', 
				'movement' => 1, 
				'correctExceptions' => ['\Exception'],
				'tries' => 3, 
				'interval' => 100,
				'backoff' => 50,
				'counter' => 3,
				'expectedTime' => 250000,
				'executionShift' => 5*1000 
			],
			[
				'method'=> 'runWithRuntimeException', 
				'movement' => 2, 
				'correctExceptions' => ['\RuntimeException'],
				'tries' => 5, 
				'interval' => 150,
				'backoff' => 0,
				'counter' => 10,
				'expectedTime' => 600000,
				'executionShift' => 6*1000 
			]
		];
		foreach ($cases as $caseSettings) {
			self::$exceptionProvider->reset();
			try {
				$time->startWatching();
				\core\utils\Retrying::retry(
					[self::$exceptionProvider, $caseSettings['method']], 
					[$caseSettings['movement']], 
					$caseSettings['correctExceptions'], 
					$caseSettings['tries'], 
					$caseSettings['interval'], 
					$caseSettings['backoff']
				);
			} catch (\Exception $error) {
				$time->stopWatching();
				var_dump(
					"Running {$caseSettings['method']} in {$caseSettings['tries']} tries.\n"
					. "Expecting time {$caseSettings['expectedTime']}, "
					. "shift {$caseSettings['executionShift']}.\n"
					. "Real time: " . $time->getTimeMks() 
					. ". Real shift: " . $time->getDeltaMks($caseSettings['expectedTime']) . "\n"
					. "Expecting counter {$caseSettings['counter']}. Real counter: " 
					. self::$exceptionProvider->getCounter()
				);
				$this->assertEquals(
					$caseSettings['counter'], 
					self::$exceptionProvider->getCounter()
				);
				$this->assertLessThanOrEqual(
					$caseSettings['executionShift'], 
					$time->getDeltaMks($caseSettings['expectedTime'])
				);
			}
		}
	}
}