import React from 'react';
import { Field } from 'react-final-form';

const passthrough = (children, props) =>
  React.Children.map(children, (child) => React.cloneElement(child, props));

const Condition = ({ when, is, match, children, fallback, ...rest }) => (
  <Field name={when}>
    {({ input: { value }, meta }) => {
      if (typeof is !== 'undefined') {
        return value === is ? passthrough(children, rest) : fallback || null;
      }

      if (typeof match === 'function') {
        return match(value, meta) ? passthrough(children, rest) : fallback || null;
      }

      return fallback || null;
    }}
  </Field>
);

export default Condition;
