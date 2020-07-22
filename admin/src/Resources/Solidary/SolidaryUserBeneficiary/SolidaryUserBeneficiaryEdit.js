import React from 'react';
import { Link } from 'react-router-dom';
import ContentCreate from '@material-ui/icons/Create';

import {
  Edit,
  TabbedForm,
  FormTab,
  Toolbar,
  SaveButton,
  TextInput,
  useTranslate,
  Button,
  SelectInput,
  DateInput,
} from 'react-admin';

import { DiariesTable } from '../Solidary/view/DiariesTable';
import { ValidateCandidateInput } from '../SolidaryUserVolunteer/Input/ValidateCandidateInput';

const SolidaryUserBeneficiaryEditToolbar = (props) => (
  <Toolbar {...props}>
    {props.tabIndex === 0 && (
      <>
        <Button
          variant="contained"
          color="primary"
          component={Link}
          label="ra.action.edit"
          icon={<ContentCreate />}
          to={`/users/${encodeURIComponent(props.record.user.id)}`}
        />
        &nbsp;
        <Button
          variant="contained"
          color="primary"
          component={Link}
          label="Demandes solidaires"
          to={`/solidaries?filter=${encodeURIComponent(
            JSON.stringify({ solidaryUser: `/solidary_users/${props.record.originId}` })
          )}`}
        />
      </>
    )}
    {props.tabIndex === 2 && <ValidateCandidateInput source="validatedCandidate" />}
    {props.tabIndex === 2 && <SaveButton />}
  </Toolbar>
);

const Diaries = ({ record }) => (
  <div style={{ maxHeight: 500, overflowY: 'scroll' }}>
    <DiariesTable version="beneficiary" diaries={record.diaries} />
  </div>
);

const getTabIndex = (pathname) => {
  const urlParts = pathname.split('/');
  const urlTab = urlParts[urlParts.length - 1];
  return !isNaN(parseFloat(urlTab)) && isFinite(urlTab) ? parseInt(urlTab, 10) : 0;
};

export const SolidaryUserBeneficiaryEdit = (props) => {
  const translate = useTranslate();

  const genderChoices = [
    { id: 1, name: translate('custom.label.user.choices.women') },
    { id: 2, name: translate('custom.label.user.choices.men') },
    { id: 3, name: translate('custom.label.user.choices.other') },
  ];

  return (
    <Edit {...props} title="Demandeurs solidaire > editer">
      <TabbedForm
        toolbar={
          <SolidaryUserBeneficiaryEditToolbar
            tabIndex={getTabIndex(props.history.location.pathname)}
          />
        }
      >
        <FormTab label="Identité">
          <TextInput disabled source="email" />
          <TextInput disabled source="telephone" />
          <TextInput disabled source="givenName" />
          <TextInput disabled source="familyName" />
          <SelectInput
            disabled
            source="gender"
            label={'custom.label.user.gender'}
            choices={genderChoices}
          />
          <DateInput disabled source="birthDate" />
        </FormTab>
        <FormTab label="Journal de suivi">
          <Diaries />
        </FormTab>
        <FormTab label="Éligibilité solidaire">
          <span>{/* Proofs here... */}</span>
        </FormTab>
      </TabbedForm>
    </Edit>
  );
};
