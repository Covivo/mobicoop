import React from 'react';
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles';

import {
  Create,
  SimpleForm,
  required,
  TextInput,
  BooleanInput,
  ReferenceInput,
  SelectInput,
  DateInput,
  useTranslate,
  AutocompleteInput,
} from 'react-admin';

import GeocompleteInput from '../../components/geolocation/geocomplete';
import { validationChoices } from './communityChoices';
import CommunityImageUpload from './CommunityImageUpload';

const useStyles = makeStyles({
  hiddenField: { display: 'none' },
  fullwidth: { width: '100%', marginBottom: '1rem' },
  fullwidthDense: { width: '100%' },
  richtext: { width: '100%', minHeight: '15rem', marginBottom: '1rem' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
});

export const CommunityCreate = (props) => {
  const classes = useStyles();
  const translate = useTranslate();
  const user = `/users/${localStorage.getItem('id')}`;

  const inputText = (user) =>
    user ? `${user.givenName} ${user.familyName || user.shortFamilyName}` : '';

  return (
    <Create {...props} title="Communautés > ajouter">
      <SimpleForm initialValues={{ user }} redirect="list">
        <TextInput
          fullWidth
          source="name"
          label={translate('custom.label.community.name')}
          validate={required()}
          formClassName={classes.title}
        />
        <CommunityImageUpload
          label={translate('custom.label.event.uploadImage') + ' (2Mb max)'}
          formClassName={classes.fullwidth}
        />
        <GeocompleteInput
          source="address"
          label={translate('custom.label.community.adress')}
          validate={required()}
          formClassName={classes.fullwidth}
        />
        <BooleanInput
          source="membersHidden"
          label={translate('custom.label.community.memberHidden')}
        />
        <BooleanInput
          source="proposalsHidden"
          label={translate('custom.label.community.proposalHidden')}
        />
        <SelectInput
          source="validationType"
          label={translate('custom.label.community.validationType')}
          choices={validationChoices}
        />
        <TextInput
          fullWidth
          source="domain"
          label={translate('custom.label.community.domainName')}
        />
        <TextInput
          fullWidth
          source="description"
          label={translate('custom.label.community.description')}
          validate={required()}
          formClassName={classes.fullwidth}
        />
        <RichTextInput
          fullWidth
          variant="filled"
          source="fullDescription"
          label={translate('custom.label.community.descriptionFull')}
          validate={required()}
          formClassName={classes.richtext}
        />
        <DateInput
          disabled
          source="createdDate"
          label={translate('custom.label.community.createdDate')}
        />
        <DateInput
          disabled
          source="updatedDate"
          label={translate('custom.label.community.updateDate')}
        />
        {/* <TextInput disabled source="status" label={translate('custom.label.community.status')} />*/}
        <ReferenceInput
          fullWidth
          source="user"
          label={translate('custom.label.community.createdBy')}
          reference="users"
        >
          {/* Should be like that : 
              <AutocompleteInput inputText={inputText} optionValue="id" optionText={<FullNameField />} matchSuggestion={(filterValue, suggestion) => true} allowEmpty={false}/>
              But https://github.com/marmelab/react-admin/pull/4367
              So waiting for the next release of react-admin 
          */}
          <AutocompleteInput optionValue="id" optionText={inputText} allowEmpty={false} />
        </ReferenceInput>
      </SimpleForm>
    </Create>
  );
};
