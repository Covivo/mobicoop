import React from 'react';
import { makeStyles } from '@material-ui/core/styles';

import {
  Edit,
  SimpleForm,
  ReferenceInput,
  SelectInput,
  required,
  AutocompleteInput,
} from 'react-admin';

import { UserRenderer } from '../../utils/renderers';
import { statusChoices } from '../Community/communityChoices';

const useStyles = makeStyles({
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
});

export const CommunityUserEdit = (props) => {
  const classes = useStyles();
  const redirect = props.location.backTo || '/communities/';

  const inputText = ({ user }) =>
    user ? `${user.givenName} ${user.familyName || user.shortFamilyName}` : '';

  return (
    <Edit {...props} title="Communautés > éditer un membre">
      <SimpleForm redirect={redirect}>
        <ReferenceInput
          fullWidth
          label="Communauté"
          source="community"
          reference="communities"
          validate={required()}
          formClassName={classes.title}
        >
          <SelectInput optionText="name" />
        </ReferenceInput>

        <ReferenceInput
          label="Nouveau Membre"
          source="user"
          reference="solidary_users"
          validate={required()}
          formClassName={classes.halfwidth}
        >
          {/* Should be like that : 
              <AutocompleteInput inputText={inputText} optionValue="id" optionText={<FullNameField />} matchSuggestion={(filterValue, suggestion) => true} allowEmpty={false}/>
              But https://github.com/marmelab/react-admin/pull/4367
              So waiting for the next release of react-admin 
          */}
          <AutocompleteInput optionValue="user.id" optionText={inputText} allowEmpty={false} />
        </ReferenceInput>

        <SelectInput
          label="Statut"
          source="status"
          choices={statusChoices}
          defaultValue={1}
          validate={required()}
        />
      </SimpleForm>
    </Edit>
  );
};
