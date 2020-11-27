import React from 'react';
import { makeStyles } from '@material-ui/core/styles';

import {
  Show,
  SimpleShowLayout,
  Labeled,
  RichTextField,
  TextField,
  BooleanField,
  ReferenceField,
  SelectField,
  ImageField,
  DateField,
  FunctionField,
  UrlField,
  useTranslate
} from 'react-admin';

import { addressRenderer, UserRenderer } from '../../utils/renderers';

const useStyles = makeStyles({
  form: { display: 'flex', flexWrap: 'wrap' },
  imagewidth: { width: '150px' },
  quarterwidth: { width: '25%' },
  fullwidth: { width: '100%' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
});

export const EventShow = (props) => {
  const classes = useStyles();
  const translate = useTranslate();
  return (
    <Show {...props} title="Evénement > afficher">
      <SimpleShowLayout className={classes.form}>
        <ReferenceField
          reference="images"
          source="images[0].id"
          addLabel={false}
          className={classes.fullwidth}
        >
          <ImageField source="versions.square_100" />
        </ReferenceField>
        <TextField source="name" className={classes.title} addLabel={false} />
        <TextField source="description" addLabel={false} className={classes.fullwidth} />
        <RichTextField source="fullDescription" addLabel={false} />
        <UrlField source="url" className={classes.fullwidth} label="Site internet" />
        <FunctionField
          label="Adresse"
          className={classes.fullwidth}
          render={(r) => addressRenderer(r.address)}
        />
        <DateField
          source="fromDate"
          label="Date de début"
          showTime
          className={classes.quarterwidth}
        />
        <DateField source="toDate" label="Date de fin" showTime className={classes.quarterwidth} />

        <Labeled label="Créé par" className={classes.quarterwidth}>
          <FunctionField render={({ user }) => <UserRenderer record={user} />} />
        </Labeled>

        <SelectField
          source="status"
          label="Etat"
          className={classes.quarterwidth}
          choices={[
            { id: 0, name: 'Brouillon' },
            { id: 1, name: 'Validé' },
            { id: 2, name: 'Désactivé' },
          ]}
        />
        <BooleanField
          source="private"
          label={translate('custom.label.event.private')}
        />
      </SimpleShowLayout>
    </Show>
  );
};
