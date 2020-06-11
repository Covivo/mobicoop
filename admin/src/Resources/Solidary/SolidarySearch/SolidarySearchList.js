import React from 'react';
import get from 'lodash/get';
import { List, Datagrid, TextField, DateField, BooleanField, Mutation, Button } from 'react-admin';
import { Chip, Grid, Typography } from '@material-ui/core';

const ScheduleDisplay = ({ record, source }) => {
  const schedule = get(record, source);
  if (schedule) {
    return (
      <Grid container spacing={1}>
        {Object.keys(schedule).map((e) => (
          <Grid container>
            <Grid item xs={4}>
              <Chip color="primary" label={e} />
            </Grid>
            <Grid item xs={8}>
              <Typography variant="body2" gutterBottom>
                Début : {schedule[e].minTime && new Date(schedule[e].minTime).toLocaleString()}
                <br />
                Fin : {schedule[e].maxTime && new Date(schedule[e].maxTime).toLocaleString()}
              </Typography>
            </Grid>
          </Grid>
        ))}
      </Grid>
    );
  }
  return null;
};

const RoleDisplay = ({ record, source }) => {
  const type = get(record, source);
  switch (type) {
    case 1:
      return (
        <Typography variant="body2" gutterBottom>
          Conducteur
        </Typography>
      );
    case 2:
      return (
        <Typography variant="body2" gutterBottom>
          Passager
        </Typography>
      );
    default:
      return (
        <Typography variant="body2" gutterBottom>
          Peu importe
        </Typography>
      );
  }
};

const FrequencyDisplay = ({ record, source }) => {
  const type = get(record, source);
  switch (type) {
    case 1:
      return (
        <Typography variant="body2" gutterBottom>
          Occasionnel
        </Typography>
      );
    default:
      return (
        <Typography variant="body2" gutterBottom>
          Régulier
        </Typography>
      );
  }
};

const CreateSolidarySolutionButton = ({ record, source }) => {
  const solidaryMatching = get(record, source);
  return (
    <Mutation
      type="create"
      resource="solidary_solutions"
      payload={{ data: { solidaryMatching: solidaryMatching } }}
      options={{
        onSuccess: {
          notification: { body: 'Trajet ajouté à la demande !' },
        },
        onFailure: {
          notification: {
            body: 'Erreur : le trajet ne peut pas être ajouté à la demande.',
            level: 'warning',
          },
        },
      }}
    >
      {(approve, { loading }) => (
        <Button label="Sélectionner" onClick={approve} disabled={loading} />
      )}
    </Mutation>
  );
};

const SolidarySearchList = (props) => {
  console.log('SolidarySearchList:', props);
  const filter =
    props.location.search &&
    props.location.search.startsWith('?filter=') &&
    decodeURIComponent(props.location.search.slice(8));

  if (filter.includes('carpool')) {
    return (
      <List {...props} title="Liste des covoiturages" perPage={25}>
        <Datagrid>
          <TextField label="Auteur" source="solidaryResultCarpool.author" />
          <TextField label="Origine" source="solidaryResultCarpool.origin" />
          <TextField label="Destination" source="solidaryResultCarpool.destination" />
          <DateField label="Date" source="solidaryResultCarpool.date" />
          <FrequencyDisplay label="Fréquence" source="solidaryResultCarpool.frequency" />
          <RoleDisplay label="Role" source="solidaryResultCarpool.role" />
          <BooleanField
            label="Solidaire exclusif"
            source="solidaryResultCarpool.solidaryExlusive"
          />
          <ScheduleDisplay source="solidaryResultCarpool.schedule" label="Horaires" />
          <CreateSolidarySolutionButton source="solidaryMatching.@id" label="Action" />
        </Datagrid>
      </List>
    );
  }
  return (
    <List {...props} title="Liste des transports" perPage={25}>
      <Datagrid>
        <TextField label="Auteur" source="solidaryResultTransport.author" />
        <TextField label="Origine" source="solidaryResultTransport.origin" />
        <TextField label="Destination" source="solidaryResultTransport.destination" />
      </Datagrid>
    </List>
  );
};

export default SolidarySearchList;
