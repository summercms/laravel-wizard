<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ycs77\LaravelWizard\Wizard;

abstract class Step
{
    /** @var Wizard */
    protected $wizard;

    /** @var int */
    protected $index;

    /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null */
    protected $model;

    /** @var array */
    protected $exceptInputs = [];

    public function __construct(Wizard $wizard, int $index)
    {
        $this->wizard = $wizard;
        $this->index = $index;
    }

    public function slug(): string
    {
        if (property_exists($this, 'slug')) {
            return $this->slug;
        }

        return Str::kebab(class_basename(static::class));
    }

    public function index(): int
    {
        return $this->index;
    }

    public function number(): int
    {
        return $this->index + 1;
    }

    public function label(): string
    {
        if (property_exists($this, 'label')) {
            return $this->label;
        }

        return ucfirst(str_replace('-', ' ', Str::snake($this->slug())));
    }

    public function data($key = '')
    {
        return $this->wizard->getSteps()->data($this->slug(), $key);
    }

    // public function init(Request $request)
    // {
    //     $this->setModel($request);

    //     return $this;
    // }

    // /**
    //  * Get the step model instance or the relationships instance.
    //  *
    //  * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
    //  */
    // public function getModel()
    // {
    //     return $this->model;
    // }

    // /**
    //  * Set the step model instance or the relationships instance.
    //  */
    // public function setModel(Request $request)
    // {
    //     $this->model = $this->model($request);

    //     return $this;
    // }

    // /**
    //  * Set the step model instance or the relationships instance.
    //  *
    //  * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
    //  */
    // abstract public function model(Request $request);

    // /**
    //  * Save this step form data.
    //  *
    //  * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null  $model
    //  */
    // abstract public function save(Request $request, array $data = null, $model = null);

    public function view(): string
    {
        if (property_exists($this, 'view')) {
            return $this->view;
        }

        return config('wizard.step_view_path').".{$this->wizard->name()}.{$this->slug()}";
    }

    public function rules(Request $request): array
    {
        return [];
    }

    public function validateMessages(Request $request): array
    {
        return [];
    }

    public function validateAttributes(Request $request): array
    {
        return [];
    }
}
