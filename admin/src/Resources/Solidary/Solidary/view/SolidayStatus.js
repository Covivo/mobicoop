import React from 'react';
import PropTypes from 'prop-types';
import { Chip } from '@material-ui/core';
import HourglassEmptyIcon from '@material-ui/icons/HourglassEmpty';

/*
0 : initiated
1 : pending
2 : accepted
3 : declined

*/

const SolidaryStatus = ({ status }) => {
  switch (status) {
    case 1:
      return (
        <Chip variant="outlined" size="small" icon={<HourglassEmptyIcon />} label="En attente" />
      );
    case 2:
      return (
        <Chip
          variant="outlined"
          size="small"
          icon={<HourglassEmptyIcon />}
          color="primary"
          label="Accepté"
        />
      );
    case 3:
      return (
        <Chip
          variant="outlined"
          size="small"
          icon={<HourglassEmptyIcon />}
          color="secondary"
          label="Refusé"
        />
      );
    default:
      return (
        <Chip
          variant="outlined"
          size="small"
          icon={<HourglassEmptyIcon />}
          label="Contact initié"
        />
      );
  }
};

SolidaryStatus.propTypes = {
  status: PropTypes.number.isRequired,
};

export default SolidaryStatus;
