import React from 'react';
import PropTypes from 'prop-types';
import { TimeInput } from 'react-admin-date-inputs';
import DateFnsUtils from '@date-io/date-fns';
import frLocale from 'date-fns/locale/fr';
import { Grid } from '@material-ui/core';
import { useField } from 'react-final-form';

const pickerOptions = {
  ampm: false,
  clearable: true,
  autoOk: true,
};

export const AvailabilityRangeInput = ({ source, ...props }) => {
  const minTimeSource = `${source}MinTime`;
  const maxTimeSource = `${source}MaxTime`;

  const minTime = useField(minTimeSource);

  return (
    <Grid container spacing={2}>
      <Grid item xs={6}>
        <TimeInput
          source={minTimeSource}
          label="Début"
          providerOptions={{ utils: DateFnsUtils, locale: frLocale }}
          options={{ ...pickerOptions }}
          {...props}
        />
      </Grid>
      <Grid item xs={6}>
        <TimeInput
          source={maxTimeSource}
          label="Fin"
          providerOptions={{ utils: DateFnsUtils, locale: frLocale }}
          options={{ ...pickerOptions, minTime: minTime.input.value }}
          {...props}
        />
      </Grid>
    </Grid>
  );
};

AvailabilityRangeInput.propTypes = {
  source: PropTypes.string.isRequired,
};
