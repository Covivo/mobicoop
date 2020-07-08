import RoomIcon from '@material-ui/icons/Room';
import React from 'react';
import PropTypes from 'prop-types';
import { Stepper, Step, StepLabel } from '@material-ui/core';

import SolidaryPlace from './SolidaryPlace';

export const Trip = ({ origin, destination }) => {
  return (
    <Stepper>
      <Step active key={1}>
        <StepLabel icon={<RoomIcon />}>
          <SolidaryPlace place={origin} />
        </StepLabel>
      </Step>
      <Step active key={2}>
        <StepLabel icon={<RoomIcon />}>
          <SolidaryPlace place={destination} />
        </StepLabel>
      </Step>
    </Stepper>
  );
};

Trip.propTypes = {
  origin: PropTypes.string,
  destination: PropTypes.string,
};
