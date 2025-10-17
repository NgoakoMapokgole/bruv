<dialog id = "reviewModal" class = "reviewForm">
      <section class = "reviewContent">
        <span class = "close">&times;</span>

        <h2>Write a Review</h2>
        <form action="../reviewForm.php" method="POST" enctype="multipart/form-data">
          <!-- left column -->
          <section class="form-left">
            <section class="form-group">
              <label for="review">Title</label>
              <input type="text" name="title" placeholder="Title..." required>
            </section>

            <section class="form-group">
              <label for="reviewContent">Content</label>
              <textarea name="content" id="reviewContent" placeholder="Add a review..." required></textarea>
            </section>

            <section class="form-group">
              <label for="category">Category</label>
              <select id="category" name="category" required>
                <option value="">-- Select Category --</option>
                <option value="Place">Place</option>
                <option value="Food">Food</option>
                <option value="Media">Media</option>
                <option value="Concept">Concept</option>
                <option value="EverythingElse">Everything Else</option>
              </select>
            </section>

            <section class="form-group">
              <label for="rating">Rating</label>
              <section class="star-rating">
                <!-- Default rating selected (5 stars) to prevent NULL -->
                <input type="radio" id="star5" name="rating" value="5" checked />
                <label for="star5" title="5 stars">★</label>

                <input type="radio" id="star4" name="rating" value="4" />
                <label for="star4" title="4 stars">★</label>

                <input type="radio" id="star3" name="rating" value="3" />
                <label for="star3" title="3 stars">★</label>

                <input type="radio" id="star2" name="rating" value="2" />
                <label for="star2" title="2 stars">★</label>

                <input type="radio" id="star1" name="rating" value="1" />
                <label for="star1" title="1 star">★</label>
              </section>
            </section>
            </section>


            <!-- right column -->
          <section class="form-right">
              <section class="form-group">
                <!-- Single Media Upload -->
                <label for="media">Upload Media:</label>
                <input type="file" id="media" name="media[]" accept="image/*,video/*,audio/*" multiple>

                <!-- Preview Area -->
                <section id="previewArea"></section>
              </section>

              <section class="form-group">
                <label for="tagInput">Add tags:</label>
                <input type="text" id="tagInput" list="tagSuggestions" placeholder="Type or pick a tag and press Enter">
                <datalist id="tagSuggestions"></datalist>
                <section id="tagContainer"></section>

                <!-- hidden input to submit tags -->
                <input type="hidden" name="tags" id="tagsField">
              </section>
          </section>

          <section class="form-group full-width" >
            <button type="submit" class="">Post Review</button>
          </section>
        </form>
      </section>
    </dialog>

<script src="review.js"></script>