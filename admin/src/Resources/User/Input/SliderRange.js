import React from 'react';

import { useInput, useTranslate } from 'react-admin';
import Typography from '@material-ui/core/Typography';
import Slider from '@material-ui/core/Slider';

const SliderRange = (props) => {
  const {
    input: { name, onChange, value },
    meta: { touched, error },
  } = useInput(props);

  const translate = useTranslate();
  //Use for set the range value by default, cause we need it to be defined for api
  if (!value) {
    onChange(1);
  }
  return (
    <>
      <Typography style={{ color: '#5e5e5e', marginTop: '10px' }}>
        {translate('custom.label.user.filter.range')}
      </Typography>
      <Slider
        defaultValue={value ? value : 1}
        aria-labelledby="discrete-slider"
        valueLabelDisplay="auto"
        step={1}
        marks
        min={1}
        max={25}
        label="rayon"
        name="rayon"
        onChangeCommitted={(event, newValue) => onChange(newValue)}
      />
    </>
  );
};

export default SliderRange;
