import React, { useCallback } from 'react';
import PropTypes from 'prop-types';

import { SaveButton } from 'react-admin';
import { useForm } from 'react-final-form';

const SaveSolidaryAsk = ({ handleSubmitWithRedirect, ...props }) => {
  const form = useForm();

  const dateObjectToString = (date) => {
    if (date && typeof date === 'object' && date.toJSON) {
      return date.toJSON();
    }
    if (date && typeof date === 'string') {
      return new Date(date).toJSON();
    }
    return null;
  };

  const handleClick = useCallback(() => {
    const { values } = form.getState();
    // Format datetimes objects
    form.change('outwardDatetime', dateObjectToString(values.outwardDatetime));
    form.change('outwardDeadlineDatetime', dateObjectToString(values.outwardDeadlineDatetime));
    form.change('returnDatetime', dateObjectToString(values.returnDatetime));
    form.change('returnDeadlineDatetime', dateObjectToString(values.returnDeadlineDatetime));
    // remove days if frequency=1 (punctual)
    if (values.frequency === 1) {
      form.change('days', null);
    }

    handleSubmitWithRedirect('list');
  }, [form, handleSubmitWithRedirect]);

  // override handleSubmitWithRedirect with custom logic
  return <SaveButton {...props} handleSubmitWithRedirect={handleClick} />;
};

SaveSolidaryAsk.propTypes = {
  handleSubmitWithRedirect: PropTypes.func.isRequired,
};

export default SaveSolidaryAsk;
