import React from 'react';
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles';

import {
  Edit,
  SimpleForm,
  TextInput,
  BooleanInput,
  SelectInput,
  ReferenceField,
  FunctionField,
  useTranslate,
  ImageField,
} from 'react-admin';

import { addressRenderer } from '../../utils/renderers';
import GeocompleteInput from '../../components/geolocation/geocomplete';
import EventImageUpload from './EventImageUpload';
import EventDuration from './EventDuration';

const useStyles = makeStyles({
  fullwidth: { width: '100%' },
  spacedFullwidth: { width: '100%', marginBottom: '1rem' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
  richtext: { width: '100%', minHeight: '15rem', marginBottom: '1rem' },
  inlineBlock: { display: 'inline-flex', marginRight: '1rem' },
  footer: { marginTop: '2rem' },
});

export const EventEdit = (props) => {
  const translate = useTranslate();
  const classes = useStyles();

  const required = (message = 'ra.validation.required') => (value) =>
    value ? undefined : translate(message);

  return (
    <Edit {...props} title={translate('custom.label.event.title.edit')}>
      <SimpleForm>
        <TextInput
          fullWidth
          source="name"
          label={translate('custom.label.event.name')}
          validate={[required()]}
          required
          formClassName={classes.title}
        />
        <BooleanInput
          label={translate('custom.label.event.private')}
          source="private"
        />
        <ReferenceField
          reference="images"
          source="images[0].id"
          label={translate('custom.label.event.currentImage')}
        >
          <ImageField source="versions.square_250" />
        </ReferenceField>
        <EventImageUpload
          label={translate('custom.label.event.changeImage')}
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
          formClassName={classes.richtext}
        />
        <TextInput
          fullWidth
          source="url"
          type="url"
          label={translate('custom.label.event.site')}
          formClassName={classes.spacedFullwidth}
        />

        <FunctionField
          label={translate('custom.label.event.currentAdresse')}
          render={(r) => addressRenderer(r.address)}
        />
        <GeocompleteInput
          source="address"
          label={translate('custom.label.event.newAdresse')}
          validate={required()}
          formClassName={classes.spacedFullwidth}
        />
        <BooleanInput
          label={translate('custom.label.event.setTime')}
          source="setTime"
          formClassName={classes.inlineBlock}
        />
        <EventDuration formClassName={classes.inlineBlock} />
        <SelectInput
          source="status"
          choices={[
            { id: 0, name: translate('custom.label.event.statusChoices.draft') },
            { id: 1, name: translate('custom.label.event.statusChoices.enabled') },
            { id: 2, name: translate('custom.label.event.statusChoices.disabled') },
          ]}
          formClassName={classes.inlineBlock}
        />
      </SimpleForm>
    </Edit>
  );
};
