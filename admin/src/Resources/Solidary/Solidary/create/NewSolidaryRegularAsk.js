import React, { useState, useEffect } from 'react';
import { Button, Grid, Card, TextField } from '@material-ui/core';
import Box from '@material-ui/core/Box';
import { useInput } from 'react-admin';
import { makeStyles } from '@material-ui/core/styles';
import { useForm } from 'react-final-form';
import DeleteIcon from '@material-ui/icons/Delete';
import DayChipInput from './DayChipInput';
import SolidaryRegularSchedules from './SolidaryRegularSchedules';

const BoundedDateTimeField = (props) => {
  const {
    input: { name, onChange, ...rest },
    meta: { touched, error },
  } = useInput(props);

  return (
    <TextField
      name={name}
      label={props.label}
      type="time"
      InputLabelProps={{ shrink: true }}
      onChange={onChange}
      error={!!(touched && error)}
      helperText={touched && error}
      {...rest}
    />
  );
};

const NewSolidaryRegularAsk = (props) => {
  return (
    <>
      <SolidaryRegularSchedules />
    </>
  );
};

export default NewSolidaryRegularAsk;
