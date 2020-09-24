import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { parse } from 'query-string';

import {
  Create,
  SimpleForm,
  required,
  ReferenceInput,
  SelectInput,
  AutocompleteInput,
} from 'react-admin';

import { statusChoices } from '../Community/communityChoices';

const useStyles = makeStyles({
  halfwidth: { width: '50%', marginBottom: '1rem' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
});

export const CommunityUserCreate = (props) => {
  const classes = useStyles();
  const { community: community_string } = parse(props.location.search);
  const community = `/communities/${community_string}`;

  const community_uri = encodeURIComponent(community);
  const redirect = community_uri ? `/communities/${community_uri}` : 'show';

  const inputText = (user) =>
    user ? `${user.givenName} ${user.familyName || user.shortFamilyName}` : '';

  return (
    <Create {...props} title="Communautés > ajouter un membre">
      <SimpleForm defaultValue={{ community }} redirect={redirect}>
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
          reference="users"
          validate={required()}
          formClassName={classes.halfwidth}
          filterToQuery={(searchText) => ({ familyName: [searchText] })}
        >
          {/* Should be like that : 
              <AutocompleteInput inputText={inputText} optionValue="id" optionText={<FullNameField />} matchSuggestion={(filterValue, suggestion) => true} allowEmpty={false}/>
              But https://github.com/marmelab/react-admin/pull/4367
              So waiting for the next release of react-admin 
          */}
          <AutocompleteInput optionText={inputText} allowEmpty={false} />
        </ReferenceInput>

        <SelectInput
          label="Statut"
          source="status"
          choices={statusChoices}
          defaultValue={1}
          validate={required()}
          formClassName={classes.halfwidth}
        />
      </SimpleForm>
    </Create>
  );
};
