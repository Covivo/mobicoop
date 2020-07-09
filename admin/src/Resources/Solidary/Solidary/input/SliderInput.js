import React from 'react';
import { useInput } from 'react-admin';
import { Slider } from '@material-ui/core';

export const SliderInput = ({
  allowEmpty,
  choices = [],
  classes: classesOverride,
  className,
  disableValue,
  emptyText,
  emptyValue,
  format,
  helperText,
  label,
  onBlur,
  onChange,
  onFocus,
  options,
  optionText,
  optionValue,
  parse,
  resource,
  source,
  translateChoice,
  validate,
  ...rest
}) => {
  const { input } = useInput({
    format,
    onBlur,
    onChange,
    onFocus,
    parse,
    resource,
    source,
    validate,
    ...rest,
  });

  const marks = choices.map((label, index) => ({
    value: index,
    label,
  }));

  return (
    <Slider
      value={input.value}
      defaultValue={0}
      onChange={(_, value) => input.onChange(value)}
      step={1}
      max={marks.length - 1}
      marks={marks}
    />
  );
};
