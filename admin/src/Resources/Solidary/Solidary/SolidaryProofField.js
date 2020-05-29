import React from 'react';
import PropTypes from 'prop-types';
import { SelectInput, TextInput, BooleanInput, FileInput, FileField } from 'react-admin';

const SolidaryProofField = ({ proof, ...rest }) => {
  if (proof.checkbox) {
    return <BooleanInput label={proof.label} source={proof.id} {...rest} />;
  }
  if (proof.input) {
    return <TextInput label={proof.label} source={proof.id} {...rest} />;
  }
  if (proof.selectbox) {
    const selectboxLabels = proof.options.split(';');
    const selectboxIds = proof.acceptedValues
      .split(';')
      .map((v, i) => ({ id: v, name: selectboxLabels[i] }));

    return <SelectInput label={proof.label} source={proof.id} choices={selectboxIds} {...rest} />;
  }
  if (proof.file) {
    return (
      <FileInput source={proof.id} label={proof.label} accept="application/pdf" {...rest}>
        <FileField source="src" title="title" />
      </FileInput>
    );
  }

  return null;
};

SolidaryProofField.propTypes = {
  proof: PropTypes.object.isRequired,
};

export default SolidaryProofField;
