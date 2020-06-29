import React from 'react';
import { useTranslate } from 'react-admin';
import PhoneIcon from '@material-ui/icons/Phone';

import { Grid, Avatar, makeStyles, ListItem, ListItemText, Paper } from '@material-ui/core';

import { formatPhone } from '../../SolidaryUserBeneficiary/Fields/PhoneField';
import { SolidaryContactDropDown } from './SolidaryContactDropDown';
import { solidaryDriverTypeLabels } from '../../../../constants/solidaryDriverType';
import SolidarySchedule from './SolidarySchedule';

import {
  solidaryAskStatusLabels,
  solidaryAskStatusIcons,
  getAskStatusColor,
} from '../../../../constants/solidaryAskStatus';

const useStyles = makeStyles({
  root: {
    position: 'relative',
    paddingTop: 40,
    minHeight: 80,
    marginBottom: 10,
    borderBottom: '1px solid #ccc',
  },
  status: { position: 'absolute', top: 10, right: 10 },
});

export const SolidaryAskRow = ({ solidary, ask }) => {
  const translate = useTranslate();
  const classes = useStyles();

  const StatusIcon = solidaryAskStatusIcons[ask.status];

  const {
    days,
    frequency,
    outwardDatetime,
    outwardDeadlineDatetime,
    returnDatetime,
    returnDeadlineDatetime,
    marginDuration,
  } = ask;

  return (
    <ListItem ContainerComponent={Paper} className={classes.root}>
      <span className={classes.status}>
        <StatusIcon style={{ verticalAlign: 'middle', color: getAskStatusColor(ask.status) }} />
        <span style={{ color: getAskStatusColor(ask.status) }}>
          &nbsp; {translate(solidaryAskStatusLabels[ask.status])}
        </span>{' '}
      </span>
      <Grid container>
        <Grid item xs={12} style={{ paddingBottom: 20 }}>
          <SolidarySchedule
            frequency={frequency}
            outwardDatetime={outwardDatetime}
            outwardDeadlineDatetime={outwardDeadlineDatetime}
            returnDatetime={returnDatetime}
            returnDeadlineDatetime={returnDeadlineDatetime}
            monCheck={days && days.mon}
            tueCheck={days && days.tue}
            wedCheck={days && days.wed}
            thuCheck={days && days.thu}
            friCheck={days && days.fri}
            satCheck={days && days.sat}
            sunCheck={days && days.sun}
            marginDuration={marginDuration}
          />
        </Grid>
        <Grid item xs={12}>
          <Grid container justify="space-between" alignItems="center">
            <Grid item xs={1}>
              <Avatar alt={ask.driver || 'Inconnu'} src="/static/images/avatar/1.jpg" />
            </Grid>
            <Grid item xs={3}>
              <ListItemText
                primary={ask.driver}
                secondary={translate(solidaryDriverTypeLabels[ask.driverType])}
              />
            </Grid>
            <Grid xs={2} item>
              <PhoneIcon style={{ verticalAlign: 'middle' }} />
              <span>{ask.telephone ? formatPhone(ask.telephone) : '-'}</span>
            </Grid>
            <Grid xs={4} item>
              <SolidaryContactDropDown
                label="Contacter conducteur"
                solidaryId={solidary.originId}
                solidarySolutionId={ask.solidarySolutionId}
              />
            </Grid>
          </Grid>
        </Grid>
      </Grid>
    </ListItem>
  );
};
