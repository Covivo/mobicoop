import React from 'react';
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles';

import {
  Create,
  SimpleForm,
  TextInput,
  SelectInput,
  BooleanInput,
  useTranslate,
} from 'react-admin';

import GeocompleteInput from '../../components/geolocation/geocomplete';
import EventImageUpload from './EventImageUpload';
import EventDuration from './EventDuration';
import CurrentUserInput from '../User/Input/CurrentUserInput';

const useStyles = makeStyles({
  inlineBlock: { display: 'inline-flex', marginRight: '1rem' },
  fullwidth: { width: '100%', marginBottom: '1rem' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
  richtext: { width: '100%', minHeight: '15rem', marginBottom: '1rem' },
});

export const EventCreate = (props) => {
  const classes = useStyles();
  const translate = useTranslate();

  const required = (message = 'ra.validation.required') => (value) =>
    value ? undefined : translate(message);

  return (
    <Create {...props} title="Evénement > créer">
      <SimpleForm>
        <TextInput
          fullWidth
          required
          source="name"
          label={translate('custom.label.event.name')}
          validate={[required()]}
          formClassName={classes.title}
        />
        <EventImageUpload
          label={translate('custom.label.event.uploadImage')}
          formClassName={classes.fullwidth}
        />
        <TextInput
          fullWidth
          source="description"
          label={translate('custom.label.event.resume')}
          validate={required()}
          required
          formClassName={classes.fullwidth}
        />
        <RichTextInput
          variant="filled"
          source="fullDescription"
          label={translate('custom.label.event.resumefull')}
          validate={required()}
          required
          formClassName={classes.richtext}
        />

        <TextInput
          fullWidth
          source="url"
          type="url"
          label={translate('custom.label.event.site')}
          formClassName={classes.fullwidth}
        />
        <GeocompleteInput
          source="address"
          label={translate('custom.label.event.adresse')}
          validate={required()}
          required
          formClassName={classes.fullwidth}
        />

        <BooleanInput
          label={translate('custom.label.event.setTime')}
          source="useTime"
          initialValue={false}
          formClassName={classes.inlineBlock}
        />
        <EventDuration formClassName={classes.inlineBlock} />
        <SelectInput
          label={translate('custom.label.event.status')}
          source="status"
          defaultValue={1}
          choices={[
            { id: 0, name: translate('custom.label.event.statusChoices.draft') },
            { id: 1, name: translate('custom.label.event.statusChoices.enabled') },
            { id: 2, name: translate('custom.label.event.statusChoices.disabled') },
          ]}
          formClassName={classes.inlineBlock}
        />

        <CurrentUserInput source="user" label={translate('custom.label.event.creator')} />
      </SimpleForm>
    </Create>
  );
};
