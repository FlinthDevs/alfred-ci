<?php

$element += [
  '#options' => $this->getOptions($items->getEntity()),
  '#default_value' => $this->getSelectedOptions($items),
  // Do not display a 'multiple' select box if there is only one option.
  '#multiple' => $this->multiple && count($this->options) > 1,
];

// Add attributes options (color).
foreach ($element['#options'] as $group) {
  if (is_array($group)) {
    foreach ($group as $key => $option) {
      $color = explode('_', $key);

      $element['#options_attributes'][$key] = new Attribute([
        'data-class' => $color[0],
        'data-label' => $option,
      ]);
    }
  }
}
