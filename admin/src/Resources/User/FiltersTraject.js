import React, { useState, useEffect } from 'react';
import GeocompleteFilter from '../../components/geolocation/geocompleteFilter';
import Typography from '@material-ui/core/Typography';
import SliderRange from './Input/SliderRange';
import { makeStyles } from '@material-ui/core/styles';

import { useTranslate } from 'react-admin';

const useStyles = makeStyles((theme) => ({
  main_container: {
    width: '100vh',
    display: 'block',
    position: 'relative',
    padding: '10px',
    background: '#c9c9c9',
    marginBottom: '10px',
    borderBottom: '1px solid #757575',
  },
}));

const FiltersTraject = (props) => {
  const classes = useStyles();
  const translate = useTranslate();

  return (
    <div className={classes.main_container}>
      <Typography>{translate('custom.label.user.filter.rangeFilter')}</Typography>
      <GeocompleteFilter
        source="origin"
        label={translate('custom.label.user.filter.origin')}
        name="origin"
      />
      <GeocompleteFilter
        source="destination"
        label={translate('custom.label.user.filter.destination')}
        name="destination"
      />
      <SliderRange source="range" />
    </div>
  );
};

export default FiltersTraject;
