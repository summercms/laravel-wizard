<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Ycs77\LaravelWizard\Cache\CacheManager;
use Ycs77\LaravelWizard\Contracts\Cache;
use Ycs77\LaravelWizard\Step;
use Ycs77\LaravelWizard\StepsCollection;

abstract class Wizard
{
    /** @var Container */
    protected $container;

    /** @var Config */
    protected $config;

    /** @var StepsCollection */
    protected $steps;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container['config'];
    }

    /**
     * The unique name of wizard, format snake case.
     */
    public function name(): string
    {
        if (property_exists($this, 'name')) {
            return $this->name;
        }

        return Str::snake(class_basename(static::class));
    }

    public function title(): string
    {
        if (property_exists($this, 'title')) {
            return $this->title;
        }

        return ucfirst(str_replace('_', ' ', $this->name()));
    }

    abstract protected function steps(): array;

    public function getStep(string $slug): ?Step
    {
        return $this->steps->find($slug);
    }

    public function getCurrentStep(): ?Step
    {
        return $this->steps->get($this->getCurrentStepIndex());
    }

    public function getCurrentStepIndex(): ?int
    {
        return $this->steps->currentIndex();
    }

    public function hasCurrentStepIndex(): bool
    {
        return is_int($this->getCurrentStepIndex());
    }

    public function getCurrentStepWithDefault(): Step
    {
        return $this->steps->get($this->getCurrentStepIndexWithDefault());
    }

    public function getCurrentStepIndexWithDefault(): int
    {
        return $this->getCurrentStepIndex() ?? 0;
    }

    public function setCurrentStep(Step $step): self
    {
        $this->steps->setCurrent($step);

        return $this;
    }

    public function getPrevStep(): ?Step
    {
        return $this->steps->prev();
    }

    public function getNextStep(): ?Step
    {
        return $this->steps->next();
    }

    public function hasPrevStep(): bool
    {
        return $this->steps->hasPrev();
    }

    public function hasNextStep(): bool
    {
        return $this->steps->hasNext();
    }

    /**
     * @param  Step|string|null  $step
     */
    public function isCurrentStep($step = null): bool
    {
        if ($this->hasCurrentStepIndex()) {
            if ($step instanceof Step) {
                $stepIndex = $step->index();
            } elseif (is_string($step)) {
                $stepIndex = optional($this->getStep($step))->index();
            }

            return $step ? $this->getCurrentStepIndex() === $stepIndex : false;
        }

        return true;
    }

    public function isLastStep(): bool
    {
        return $this->isCurrentStep($this->steps->last());
    }

    public function needRedirectToCorrectStep(string $step = null): bool
    {
        return is_null($step) ||
            ($this->hasCurrentStepIndex() &&
            $this->getStep($step) &&
            ! $this->isCurrentStep($step));
    }

    // /**
    //  * @throws \Ycs77\LaravelWizard\Exceptions\StepNotFoundException
    //  */
    // public function isValidStep(Step $currentStep = null, string $controller)
    // {
    //     if (is_null($currentStep)) {
    //         throw new StepNotFoundException(
    //             $currentStep, $this, $controller
    //         );
    //     }
    // }

    /**
     * @throws \InvalidArgumentException
     */
    public function saveStepData(string $step, Request $request)
    {
        if ($this->steps->hasCache()) {
            $nextIndex = optional($this->getNextStep())->index();

            $this->steps->cacheData(
                $step, null, $this->getData($request), $nextIndex
            );
        } else {
            $this->saveStep(
                $this->getCurrentStep(), $this->getData($request)
            );
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function saveStepsData(Request $request)
    {
        //

        $this->steps->clearCache();
    }

    public function isLastStep(): bool
    {
        return $this->getCurrentStepIndexWithDefault() === optional($this->steps->last())->index();
    }

    // /**
    //  * @throws \Ycs77\LaravelWizard\Exceptions\StepNotFoundException
    //  */
    // public function isValidStep(Step $currentStep = null, string $controller)
    // {
    //     if (is_null($currentStep)) {
    //         throw new StepNotFoundException(
    //             $currentStep, $this, $controller
    //         );
    //     }
    // }

    /**
     * @throws \InvalidArgumentException
     */
    protected function saveStep(Step $step, array $data)
    {
        if (! method_exists($step, 'save')) {
            throw new InvalidArgumentException(sprintf(
                'Method %s does not exist.', 'save'
            ));
        }

        $this->container->call([$step, 'save'], compact('data'));
    }

    public function getData(Request $request)
    {
        return array_filter($request->all(), function ($input) {
            return ! Str::startsWith($input, '_');
        });
    }

    public function newCache(): Cache
    {
        return (new CacheManager($this->container, $this))->driver();
    }

    public function newSteps()
    {
        return new StepsCollection($this);
    }

    public function createSteps()
    {
        if (empty($steps = $this->steps())) {
            throw new InvalidArgumentException(sprintf(
                'Wizard [%s] does not contain any steps.', class_basename(static::class)
            ));
        }

        $stepsCollection = $this->newSteps();

        if ($this->config['wizard.cache']) {
            $stepsCollection->setCache($this->newCache());
        }

        return $stepsCollection->pushMany($steps);
    }

    public function getSteps(): StepsCollection
    {
        return $this->steps;
    }

    public function setSteps(StepsCollection $steps = null)
    {
        $this->steps = $steps ?? $this->createSteps();

        return $this;
    }
}
