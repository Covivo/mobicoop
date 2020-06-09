import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import RoomIcon from '@material-ui/icons/Room';
import { useShowController, ReferenceField, TextField } from 'react-admin';
import {
  Card,
  Grid,
  Avatar,
  LinearProgress,
  Button,
  Stepper,
  Step,
  StepLabel,
  Divider,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  ListItemSecondaryAction,
} from '@material-ui/core';

import DropDownButton from '../../../../components/button/DropDownButton';
import DayChip from './DayChip';
import SolidaryPlace from './SolidaryPlace';
import SolidarySchedule from './SolidarySchedule';
import SolidaryAnimation from './SolidaryAnimation';

const useStyles = makeStyles((theme) => ({
  main_panel: {
    backgroundColor: 'white',
    padding: theme.spacing(2, 4, 3),
    marginTop: '2rem',
  },
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
  progress: {
    width: '200px',
  },
  path: {
    width: '50%',
  },
  quarter: {
    width: '25%',
  },
  divider: {
    marginBottom: '1rem',
  },
}));

const SMS_CONTACT_OPTION = 'SMS';
const EMAIL_CONTACT_OPTION = 'Email';
const PHONE_CONTACT_OPTION = 'Téléphone';

const contactOptions = [SMS_CONTACT_OPTION, EMAIL_CONTACT_OPTION, PHONE_CONTACT_OPTION];

const SolidaryShow = (props) => {
  const classes = useStyles();
  const {
    basePath, // deduced from the location, useful for action buttons
    defaultTitle, // the translated title based on the resource, e.g. 'Post #123'
    loaded, // boolean that is false until the record is available
    loading, // boolean that is true on mount, and false once the record was fetched
    record, // record fetched via dataProvider.getOne() based on the id from the location
    resource, // the resource name, deduced from the location. e.g. 'posts'
    version, // integer used by the refresh feature
  } = useShowController(props);

  console.log('record:', record);
  if (!record) {
    return null;
  }

  const monCheck = false,
    tueCheck = true,
    wedCheck = true,
    thuCheck = false,
    friCheck = false,
    satCheck = false,
    sunCheck = false;
  const {
    createdDate,
    updatedDate,
    id,
    originId,
    deadlineDate,
    marginDuration,
    frequency,
    outwardDatetime,
    outwardDeadlineDatetime,
    returnDatetime,
    returnDeadlineDatetime,
    origin,
    destination,
    needs,
    subject,
    displayLabel,
    progression,
    solidaryUserStructure,
  } = record;

  const { id, criteria, user, createdDate, updatedDate } = record;
  const { monCheck, tueCheck, wedCheck, thuCheck, friCheck, satCheck, sunCheck } = criteria || {};
  const { givenName, familyName, phone, avatars = [] } = user || {};

  const handleContactChoice = (choice) => {
    console.log(`@TODO: handling ${choice}`);
  };

  return (
    <Card className={classes.main_panel}>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
              <Grid item>
                <Avatar alt="Remy Sharp" src={avatars.length && avatars[0]} />
              </Grid>
              <Grid item>
                <h2>{`${givenName} ${familyName}`}</h2>
              </Grid>
              <Grid item>
                <small>{phone}</small>
              </Grid>
              <Grid item>
                <DropDownButton
                  label="Contacter demandeur"
                  options={contactOptions}
                  onSelect={handleContactChoice}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <Button variant="contained" color="primary">
              Editer
            </Button>
          </Grid>
        </Grid>

        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <h1>{`Demande solidaire # ${originId}`}</h1>
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item className={classes.progress}>
                <LinearProgress variant="determinate" value={63} />
              </Grid>
              <Grid item>Recherche de solution</Grid>
            </Grid>
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item>
                <b>Dernière modification</b>
              </Grid>
              <Grid item>{new Date(updatedDate || createdDate).toLocaleDateString()}</Grid>
            </Grid>
          </Grid>
          <Grid item>
            <Grid
              container
              direction="column"
              justify="space-between"
              alignItems="stretch"
              spacing={1}
            >
              <Grid item>
                <b>Création</b>
              </Grid>
              <Grid item>{new Date(createdDate).toLocaleDateString()}</Grid>
            </Grid>
          </Grid>
        </Grid>
        <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
          <Grid item>
            <b>Objet du déplacement :</b>
          </Grid>
          <Grid item>{displayLabel}</Grid>
        </Grid>

        <Grid container spacing={2}>
          <Grid item md={4} xs={12}>
            {returnDatetime ? 'Aller <-> Retour' : 'Aller simple'} &nbsp;
          </Grid>

          <Grid item md={4} xs={12}>
            <SolidarySchedule
              frequency={frequency}
              outwardDatetime={outwardDatetime}
              outwardDeadlineDatetime={outwardDeadlineDatetime}
              returnDatetime={returnDatetime}
              returnDeadlineDatetime={returnDeadlineDatetime}
              monCheck={monCheck}
              tueCheck={tueCheck}
              wedCheck={wedCheck}
              thuCheck={thuCheck}
              friCheck={friCheck}
              satCheck={satCheck}
              sunCheck={sunCheck}
              marginDuration={marginDuration}
            />
          </Grid>
          <Grid item md={4} xs={12}>
            {frequency === 1 ? (
              <i>Trajet ponctuel</i>
            ) : (
              <span>
                <i>Trajet régulier</i>
                <br />
                {[
                  { label: 'L', condition: monCheck },
                  { label: 'M', condition: tueCheck },
                  { label: 'Me', condition: wedCheck },
                  { label: 'J', condition: thuCheck },
                  { label: 'V', condition: friCheck },
                  { label: 'S', condition: satCheck },
                  { label: 'D', condition: sunCheck },
                ].map(({ label, condition }) => (
                  <DayChip key={label} label={label} condition={condition} />
                ))}
              </span>
            )}
          </Grid>
        </Grid>

        <Divider light />
        <Grid container direction="row" justify="center" alignItems="center" spacing={2}>
          <Grid item lg={8} md={12} className={classes.path}>
            <Stepper>
              <Step active key={1}>
                <StepLabel icon={<RoomIcon />}>
                  <SolidaryPlace place={origin} />
                </StepLabel>
              </Step>
              <Step active key={1}>
                <StepLabel icon={<RoomIcon />}>
                  <SolidaryPlace place={destination} />
                </StepLabel>
              </Step>
            </Stepper>
          </Grid>
        </Grid>

        <Divider light className={classes.divider} />

        <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
          <Grid item xs={3}>
            <b>Autres besoins :</b>
          </Grid>
          <Grid item xs={9}>
            {needs && needs.length ? needs.map((n) => n.label).join(' ') : 'Aucun'}
          </Grid>
        </Grid>

        <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
          <Grid item md={3} xs={6}>
            <b>Structure accompagnante :</b>
          </Grid>
          <Grid item md={3} xs={6}>
            {solidaryUserStructure.structure && solidaryUserStructure.structure.name}
          </Grid>
          <Grid item md={3} xs={6}>
            <b>Opérateur ayant enregistré la demande :</b>
          </Grid>
          <Grid item md={3} xs={6}>
            Solenne Ayzel
          </Grid>
        </Grid>
      </Card>

      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <b>Conducteurs potentiels</b>
          </Grid>
          <Grid item>
            <Button variant="contained" color="primary">
              Rechercher nouveau conducteur
            </Button>
          </Grid>
        </Grid>

        <List>
          <ListItem>
            <ListItemAvatar>
              <Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg" />
            </ListItemAvatar>
            <ListItemText primary="Umberto Picaldi" secondary="+33 12346536543" />
            <ListItemSecondaryAction>
              <DropDownButton
                size="small"
                label="Contacter conducteur"
                options={contactOptions}
                onSelect={handleContactChoice}
              />
            </ListItemSecondaryAction>
          </ListItem>
          <ListItem>
            <ListItemAvatar>
              <Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg" />
            </ListItemAvatar>
            <ListItemText primary="Marcel Proust" secondary="+33 12346536543" />
            <ListItemSecondaryAction>
              <DropDownButton
                size="small"
                label="Contacter conducteur"
                options={contactOptions}
                onSelect={handleContactChoice}
              />
            </ListItemSecondaryAction>
          </ListItem>
        </List>
      </Card>

      <SolidaryAnimation record={record} />
    </Card>
  );
};

export default SolidaryShow;
