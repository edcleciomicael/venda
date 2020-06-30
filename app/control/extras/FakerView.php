<?php
class FakerView extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        $faker = Faker\Factory::create();
        
        $output = '';
        $output .= '<b>Title</b>: ' . $faker->title . '<br>';
        $output .= '<b>Name</b>: ' . $faker->name . '<br>';
        $output .= '<b>State</b>: ' . $faker->state . '<br>';
        $output .= '<b>State Abbr</b>: ' . $faker->stateAbbr . '<br>';
        $output .= '<b>City</b>: ' . $faker->city . '<br>';
        $output .= '<b>Street Name</b>: ' . $faker->streetName . '<br>';
        $output .= '<b>Number</b>: ' . $faker->buildingNumber . '<br>';
        $output .= '<b>Country</b>: ' . $faker->country . '<br>';
        
        $panel = new TPanelGroup('Faker');
        $panel->add($output);
        parent::add($panel);
    }
}
