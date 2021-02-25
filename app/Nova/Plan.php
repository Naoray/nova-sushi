<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Plan extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Plan::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Code', 'id')
                ->rules('required')
                ->creationRules([function ($attribute, $value, $fail) {
                    if (\App\Models\Plan::where('id', $value)->exists()) {
                        $fail($attribute . ' is not unique.');
                    }
                }])
                ->updateRules([function ($attribute, $value, $fail) {
                    $existsingModel = \App\Models\Plan::where('id', $value)->first();
                    if (optional($existsingModel)->isNot($this->resource)) {
                        $fail($attribute . ' is not unique.');
                    }
                }])
                ->fillUsing(fn ($request, $model, $attribute, $requestAttribute) => $model->$attribute = Str::snake($request->$requestAttribute, '-'))
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest()),

            Text::make('Nickname'),

            Text::make('Description', 'metadata->description'),

            Boolean::make('Active'),

            Number::make('Trial Period Days')
                ->min(0)
                ->nullable(),

            Currency::make('Amount')
                ->nullable()
                ->asMinorUnits()
                ->rules('required')
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest()),

            Select::make('Currency')
                ->options(['usd' => 'USD'])
                ->default('usd')
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest()),

            Select::make('Interval')
                ->options([
                    'day' => 'Day',
                    'week' => 'Week',
                    'month' => 'Month',
                    'year' => 'year',
                ])
                ->rules('required')
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest()),

            BelongsTo::make('Product', 'stripeProduct', Product::class)
                ->showCreateRelationButton()
                ->searchable(),

            KeyValue::make('Metadata')
                ->rules('json'),

            $this->metaDataPanel(false),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
