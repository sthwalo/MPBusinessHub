import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, fireEvent, screen } from '@testing-library/react';
import Modal from '../Modal';

describe('Modal Component', () => {
  const mockOnClose = vi.fn();  // Using vi.fn() since we're using Vitest

  beforeEach(() => {
    mockOnClose.mockClear();
    document.body.style.overflow = '';
  });

  it('renders modal content', () => {
    render(
      <Modal onClose={mockOnClose}>
        <div>Test Content</div>
      </Modal>
    );

    expect(screen.getByText('Test Content')).toBeInTheDocument();
  });

  it('calls onClose when escape key is pressed', () => {
    render(
      <Modal onClose={mockOnClose}>
        <div>Test Content</div>
      </Modal>
    );

    fireEvent.keyDown(document, { key: 'Escape' });
    expect(mockOnClose).toHaveBeenCalled();
  });
});