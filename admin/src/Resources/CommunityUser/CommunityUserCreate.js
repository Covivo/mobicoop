import React, { useCallback, useState } from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { parse } from 'query-string';

import {
  Create,
  SimpleForm,
  required,
  ReferenceInput,
  SelectInput,
  AutocompleteInput,
  Toolbar,
  SaveButton,
  useCreate,
  useRedirect,
  useNotify,
} from 'react-admin';

import PropTypes from 'prop-types';
import { statusChoices } from '../Community/communityChoices';

const useStyles = makeStyles({
  halfwidth: { width: '50%', marginBottom: '1rem' },
  title: { fontSize: '1.5rem', fontWeight: 'bold', width: '100%', marginBottom: '1rem' },
});

const SaveWithNoteButton = (props) => {
  const [create] = useCreate('community_users');
  const redirectTo = useRedirect();
  const notify = useNotify();
  const { basePath, userEmail, communityDomain } = props;
  let shouldTriggerAlert = false;
  let value = false;

  const handleSave = useCallback(
    (values, redirect) => {
      if (communityDomain) {
        const slug = userEmail.split('@').pop();
        if (slug !== communityDomain) {
          shouldTriggerAlert = true;
        }
      }
      if (shouldTriggerAlert) {
        value = window.confirm(
          "Attention, vous êtes en train d'ajouter un membre dans la communauté qui ne satisfait pas aux conditions de restriction de nom de domaine prévue!"
        );
      }
      if (!shouldTriggerAlert || (shouldTriggerAlert && value)) {
        create(
          {
            payload: { data: { ...values } },
          },
          {
            onSuccess: ({ data: newRecord }) => {
              notify('ra.notification.created', 'info', {
                smart_count: 1,
              });
              redirectTo(redirect, basePath, newRecord.id, newRecord);
            },
          }
        );
      }
    },
    [create, notify, redirectTo, basePath]
  );
  // set onSave props instead of handleSubmitWithRedirect
  return <SaveButton {...props} onSuccess={handleSave} />;
};

SaveWithNoteButton.propTypes = {
  userEmail: PropTypes.string.isRequired,
  communityDomain: PropTypes.string.isRequired,
  basePath: PropTypes.string.isRequired,
};

const CustomToolbar = (props) => (
  <Toolbar {...props} classes={useStyles()}>
    <SaveWithNoteButton {...props} />
  </Toolbar>
);

export const CommunityUserCreate = (props) => {
  const classes = useStyles();
  const { community: community_string } = parse(props.location.search);
  const community = `/communities/${community_string}`;

  const community_uri = encodeURIComponent(community);
  const redirect = community_uri ? `/communities/${community_uri}` : 'show';

  const [email, setEmail] = useState(null);
  const [com, setCom] = useState(null);

  const inputText = (user) => {
    setEmail(user.email);
    return user ? `${user.givenName} ${user.familyName || user.shortFamilyName}` : '';
  };

  const inputCommunityText = (commu) => {
    setCom(commu.domain);
    return commu ? commu.name : '';
  };

  return (
    <Create {...props} title="Communautés > ajouter un membre">
      <SimpleForm
        defaultValue={{ community }}
        redirect={redirect}
        toolbar={<CustomToolbar userEmail={email} communityDomain={com} />}
      >
        <ReferenceInput
          fullWidth
          label="Communauté"
          source="community"
          reference="communities"
          validate={required()}
          formClassName={classes.title}
        >
          <SelectInput optionText={inputCommunityText} />
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
