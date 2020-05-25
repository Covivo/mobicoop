import React from 'react';
import { Phone as PhoneIcon } from '@material-ui/icons';
import { Typography, Avatar, Grid, makeStyles } from '@material-ui/core';
import PropTypes from 'prop-types';

import { UserRenderer } from '../../../../utils/renderers';

const useStyles = makeStyles({
  root: {
    padding: '20px 0',
  },
});

export const UserInformationField = ({ record }) => {
  const classes = useStyles();

  const { givenName, familyName } = record;

  return (
    <div className={classes.root}>
      <Grid container spacing={4} direction="row" alignItems="center">
        <Grid item>
          <Grid container spacing={1} direction="row" alignItems="center">
            <Grid item>
              <Avatar>{givenName.slice(0, 1) + familyName.slice(0, 1)}</Avatar>
            </Grid>
            <Grid item>
              <Typography variant="h5">
                <UserRenderer record={record} />
              </Typography>
            </Grid>
          </Grid>
        </Grid>
        <Grid item>
          <Grid container spacing={1} direction="row" alignItems="center">
            <Grid item>
              <PhoneIcon />
            </Grid>
            <Grid item>
              <Typography variant="subtitle1">{record.telephone}</Typography>
            </Grid>
          </Grid>
        </Grid>
      </Grid>
    </div>
  );
};

UserInformationField.propTypes = {
  record: PropTypes.shape({
    familyName: PropTypes.string.isRequired,
    givenName: PropTypes.string.isRequired,
    telephone: PropTypes.string.isRequired,
  }).isRequired,
};
