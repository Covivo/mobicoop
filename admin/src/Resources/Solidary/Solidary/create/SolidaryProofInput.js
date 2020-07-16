import React from 'react';
import PropTypes from 'prop-types';
import { SelectInput, TextInput, BooleanInput, FileInput, FileField, required } from 'react-admin';

const SolidaryProofInput = ({ record, ...rest }) => {
  const validate = !!record.mandatory
    ? [(value) => (!value ? 'Cette preuve est obligatoire' : undefined), required()]
    : undefined;

  if (record.checkbox) {
    return (
      <BooleanInput
        validate={validate}
        label={record.label}
        source={`proofs.${record.id}`}
        {...rest}
      />
    );
  }

  if (record.input) {
    return (
      <TextInput
        validate={validate}
        label={record.label}
        source={`proofs.${record.id}`}
        {...rest}
      />
    );
  }

  if (record.selectbox) {
    const selectboxLabels = record.options.split(';');

    return (
      <SelectInput
        label={record.label}
        source={`proofs.${record.id}`}
        validate={validate}
        choices={record.acceptedValues
          .split(';')
          .map((v, i) => ({ id: v, name: selectboxLabels[i] }))}
        {...rest}
      />
    );
  }

  if (record.file) {
    return (
      <FileInput
        source={`proofs.${record.id}`}
        label={record.label}
        validate={validate}
        accept="application/pdf"
        {...rest}
      >
        <FileField source="src" title="title" />
      </FileInput>
    );
  }

  return null;
};

SolidaryProofInput.propTypes = {
  record: PropTypes.object.isRequired,
};

export default SolidaryProofInput;
