import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';

import { Avatar, Grid, Card } from '@material-ui/core';
import DayChip from './DayChip';
import SolidaryStatus from './SolidayStatus';
import { formatPhone } from '../../SolidaryUserBeneficiary/Fields/PhoneField';
import { SolidaryContactDropDown } from './SolidaryContactDropDown';

/*
  Item structure :
  --------------
  driver: "Jean-Michel Solidaire"
  driverType: 0
  frequency: 1
  fromDate: "2020-04-30T00:00:00+00:00"
  toDate: null
  messages: Array(3)
    0: {userId: 17, userFamilyName: "Solidaire", userGivenName: "Jean-Michel", text: "La question c'est est-ce que ça marche... ?", createdDate: "2020-04-30T14:14:22+00:00"}
    1: {userId: 17, userFamilyName: "Solidaire", userGivenName: "Jean-Michel", text: "La question c'est est-ce que ça marche... ?", createdDate: "2020-04-30T14:25:00+00:00"}
    2: {userId: 17, userFamilyName: "Solidaire", userGivenName: "Jean-Michel", text: "La question c'est est-ce que ça marche... ?", createdDate: "2020-04-30T14:26:00+00:00"}
  schedule: Array(1)
    0: {outwardTime: "15:57", mon: false, tue: false, wed: false, thu: false, …}
  
  solidarySolutionId: 6
  status: 0
  telephone: "0604050802"
*/

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },

  divider: {
    marginBottom: '0.5rem',
  },
}));

const SolidaryAskItem = ({ item }) => {
  const classes = useStyles();

  return (
    <Card className={classes.card}>
      <Grid
        container
        justify="space-between"
        spacing={1}
        alignItems="center"
        className={classes.divider}
      >
        <Grid item>
          {' '}
          {new Date(item.fromDate).toLocaleDateString()}
          {item.toDate ? ` -> ${new Date(item.toDate).toLocaleDateString()}` : ''}
        </Grid>
        <Grid item>
          <SolidaryStatus status={item.status} />
        </Grid>
      </Grid>
      {item.schedule &&
        item.schedule.length &&
        item.schedule.map((schedule, i) => (
          <Grid
            container
            spacing={1}
            alignItems="center"
            className={classes.divider}
            // eslint-disable-next-line react/no-array-index-key
            key={`schedule-${i}`}
          >
            <Grid item xs={12} md={4}>
              {[
                { label: 'L', condition: schedule.mon },
                { label: 'M', condition: schedule.tue },
                { label: 'Me', condition: schedule.wed },
                { label: 'J', condition: schedule.thu },
                { label: 'V', condition: schedule.fri },
                { label: 'S', condition: schedule.sat },
                { label: 'D', condition: schedule.sun },
              ].map(({ label, condition }) => (
                <DayChip key={label} label={label} condition={condition} />
              ))}
            </Grid>
            <Grid item xs={6} md={2}>
              {`Aller : ${schedule.outwardTime}`}
            </Grid>
            <Grid item xs={6} md={2}>
              {`Retour : ${schedule.returnTime || ' - '}`}
            </Grid>
          </Grid>
        ))}
      <Grid container justify="space-between" spacing={1} alignItems="center">
        <Grid item>
          <Avatar alt={item.driver || 'Inconnu'} src="/static/images/avatar/1.jpg" />
        </Grid>
        <Grid item>{item.driver || 'Inconnu'}</Grid>
        <Grid item>{item.driverType ? 'Bénévole' : 'Covoitureur'}</Grid>
        <Grid item>
          {item.telephone ? formatPhone(item.telephone) : 'pas de numéro de téléphone.'}
        </Grid>
        <Grid item>
          <SolidaryContactDropDown label="Contacter demandeur" />
        </Grid>
      </Grid>
    </Card>
  );
};

SolidaryAskItem.propTypes = {
  item: PropTypes.object.isRequired,
};
export default SolidaryAskItem;
